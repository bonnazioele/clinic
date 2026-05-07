<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Mail\ClinicApplicationReceivedMail;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ApplicationController extends Controller
{
    public function create()
    {
        $services = \App\Models\Service::orderBy('name')->get(['id', 'name']);
        $permitTypes = config('clinic_permits.types', []);

        return view('owner.apply', compact('services', 'permitTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $permitTypes = collect(config('clinic_permits.types', []));

        $rules = [
            'clinic_name' => ['required', 'string', 'max:255'],
            'clinic_address' => ['required', 'string', 'max:1024'],
            'clinic_contact' => ['required', 'string', 'max:50'],

            'branch_code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('clinics', 'branch_code')->whereNull('deleted_at'),
            ],

            'clinic_email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('clinics', 'email')->whereNull('deleted_at'),
            ],

            'contact_first_name' => ['required', 'string', 'max:255'],
            'contact_last_name' => ['required', 'string', 'max:255'],

            'contact_person_email' => [
                'required',
                'email',
                'max:255',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! $this->canReuseContactEmail((string) $value)) {
                        $fail('The contact person email has already been taken.');
                    }
                },
            ],

            /*
             |--------------------------------------------------------------------------
             | Coordinates
             |--------------------------------------------------------------------------
             | Support both naming styles:
             | - latitude / longitude
             | - gps_latitude / gps_longitude
             |
             | This prevents the clinic from saving null coordinates if your Blade form
             | uses either naming convention.
             */
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'gps_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'gps_longitude' => ['nullable', 'numeric', 'between:-180,180'],

            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],

            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => ['integer', 'exists:services,id'],
        ];

        $permitTypes->each(function ($permit) use (&$rules) {
            $key = $permit['key'];

            $requiresNumber = (bool) ($permit['requires_number'] ?? false);
            $requiresIssued = (bool) ($permit['requires_issue_date'] ?? false);
            $requiresExpiry = (bool) ($permit['requires_expiry_date'] ?? false);

            $rules["permits.$key.permit_number"] = $requiresNumber
                ? [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('clinic_permits', 'permit_number')->where('permit_type', $key),
                ]
                : [
                    'nullable',
                    'string',
                    'max:255',
                    Rule::unique('clinic_permits', 'permit_number')->where('permit_type', $key),
                ];

            $rules["permits.$key.issued_at"] = $requiresIssued
                ? ['required', 'date']
                : ['nullable', 'date'];

            $rules["permits.$key.expires_at"] = $requiresExpiry
                ? ['required', 'date', 'after_or_equal:permits.' . $key . '.issued_at']
                : ['nullable', 'date'];

            $rules["permits.$key.file"] = [
                'required',
                'file',
                'mimes:pdf,jpeg,jpg,png',
                'max:5120',
            ];
        });

        Validator::make($data, $rules, [
            'permits.*.permit_number.unique' => 'This permit number is already registered.',
            'permits.*.permit_number.required' => 'Permit number is required for this document.',
        ])->validate();

        $logoPath = null;

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('clinic-logos', 'public');
        }

        /*
         |--------------------------------------------------------------------------
         | Resolve latitude and longitude
         |--------------------------------------------------------------------------
         | First priority:
         | - coordinates sent by the applicant form
         |
         | Fallback:
         | - automatically search the clinic address using geocoding
         |
         | This is the important fix. Before, application-created clinics did not
         | automatically geocode the address, so approved clinics had no map pin.
         */
        $latitude = $request->input('latitude') ?? $request->input('gps_latitude');
        $longitude = $request->input('longitude') ?? $request->input('gps_longitude');

        if (blank($latitude) || blank($longitude)) {
            $coords = $this->geocodeAddress($data['clinic_address']);

            if ($coords) {
                $latitude = $coords['lat'];
                $longitude = $coords['lng'];
            }
        }

        $clinic = Clinic::create([
            'created_by_user_id' => null,

            'name' => to_lower($data['clinic_name']),
            'address' => $data['clinic_address'],
            'contact_number' => $data['clinic_contact'],
            'description' => null,

            'email' => $data['clinic_email'],

            'contact_first_name' => to_lower($data['contact_first_name']),
            'contact_last_name' => to_lower($data['contact_last_name']),
            'contact_person_email' => $data['contact_person_email'],

            'branch_code' => $data['branch_code'],
            'logo' => $logoPath,

            'gps_latitude' => $latitude,
            'gps_longitude' => $longitude,

            'status' => 'pending',
        ]);

        if ($request->filled('service_ids')) {
            $clinic->services()->attach($request->input('service_ids'));
        }

        $permitTypes->each(function ($permit) use ($request, $clinic) {
            $key = $permit['key'];
            $payload = $request->input("permits.$key", []);
            $file = $request->file("permits.$key.file");

            $path = $file
                ? $file->store('clinic-permits', 'public')
                : null;

            $clinic->permits()->create([
                'permit_type' => $key,
                'permit_number' => $payload['permit_number'] ?? null,
                'issued_at' => $payload['issued_at'] ?? null,
                'expires_at' => $payload['expires_at'] ?? null,
                'attachment_path' => $path,
            ]);
        });

        return redirect()->route('owner.apply.thanks');
    }

    private function geocodeAddress(string $address): ?array
    {
        $address = trim($address);

        if ($address === '') {
            return null;
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => config('app.name', 'CliniQ') . '/1.0 clinic geocoder',
                    'Accept-Language' => 'en',
                ])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $address,
                    'format' => 'jsonv2',
                    'limit' => 1,
                    'addressdetails' => 1,
                    'countrycodes' => 'ph',
                ]);

            if (! $response->successful()) {
                Log::warning('Clinic geocoding failed.', [
                    'address' => $address,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $first = $response->json()[0] ?? null;

            if (! $first || ! isset($first['lat'], $first['lon'])) {
                Log::warning('Clinic geocoding returned no coordinates.', [
                    'address' => $address,
                ]);

                return null;
            }

            return [
                'lat' => round((float) $first['lat'], 7),
                'lng' => round((float) $first['lon'], 7),
            ];
        } catch (\Throwable $e) {
            Log::warning('Clinic geocoding exception.', [
                'address' => $address,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function canReuseContactEmail(string $email): bool
    {
        $existingUser = User::where('email', $email)->first();

        if (! $existingUser) {
            return true;
        }

        if (! $existingUser->is_secretary) {
            return false;
        }

        $hasActiveOwnedClinic = Clinic::query()
            ->where('created_by_user_id', $existingUser->id)
            ->whereNull('deleted_at')
            ->exists();

        $hasActiveSecretaryClinic = $existingUser->secretaryClinics()
            ->whereNull('clinics.deleted_at')
            ->exists();

        return ! $hasActiveOwnedClinic && ! $hasActiveSecretaryClinic;
    }
}