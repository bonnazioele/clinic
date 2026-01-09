<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use App\Mail\ClinicApplicationReceivedMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ApplicationController extends Controller
{
    public function create()
    {
        $services = \App\Models\Service::orderBy('name')->get(['id','name']);
        $permitTypes = config('clinic_permits.types', []);
        return view('owner.apply', compact('services', 'permitTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        $permitTypes = collect(config('clinic_permits.types', []));

        $rules = [
            'clinic_name' => ['required','string','max:255'],
            'clinic_address' => ['required','string','max:1024'],
            'clinic_contact' => ['required','string','max:50'],
            'branch_code'   => ['required','string','max:255', Rule::unique('clinics','branch_code')->whereNull('deleted_at')],
            'clinic_email'  => ['required','email','max:255', Rule::unique('clinics','email')->whereNull('deleted_at')],
            'contact_first_name' => ['required','string','max:255'],
            'contact_last_name'  => ['required','string','max:255'],
            'contact_person_email' => ['required','email','max:255', Rule::unique('users','email')],
            'latitude'      => ['nullable','numeric','between:-90,90'],
            'longitude'     => ['nullable','numeric','between:-180,180'],
            'logo'          => ['nullable','image','mimes:jpeg,png,jpg,gif,svg','max:2048'],
            'service_ids'   => ['nullable','array'],
            'service_ids.*' => ['integer','exists:services,id'],
        ];

        $permitTypes->each(function ($permit) use (&$rules) {
            $key = $permit['key'];
            $requiresNumber = (bool) ($permit['requires_number'] ?? false);
            $requiresIssued = (bool) ($permit['requires_issue_date'] ?? false);
            $requiresExpiry = (bool) ($permit['requires_expiry_date'] ?? false);

            $rules["permits.$key.permit_number"] = $requiresNumber
                ? ['required','string','max:255']
                : ['nullable','string','max:255'];
            $rules["permits.$key.issued_at"] = $requiresIssued
                ? ['required','date']
                : ['nullable','date'];
            $rules["permits.$key.expires_at"] = $requiresExpiry
                ? ['required','date','after_or_equal:permits.'.$key.'.issued_at']
                : ['nullable','date'];
            $rules["permits.$key.file"] = ['required','file','mimes:pdf,jpeg,jpg,png','max:5120'];
        });

        Validator::make($data, $rules)->validate();

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('clinic-logos', 'public');
        }

        $clinic = Clinic::create([
            'created_by_user_id' => null,
            'name' => $data['clinic_name'],
            'address' => $data['clinic_address'],
            'contact_number' => $data['clinic_contact'],
            'description' => null,
            'email' => $data['clinic_email'],
            'contact_first_name' => $data['contact_first_name'],
            'contact_last_name' => $data['contact_last_name'],
            'contact_person_email' => $data['contact_person_email'],
            'branch_code' => $data['branch_code'],
            'logo' => $logoPath,
            'gps_latitude' => $data['latitude'] ?? null,
            'gps_longitude' => $data['longitude'] ?? null,
            'status' => 'pending',
        ]);

        if ($request->filled('service_ids')) {
            $clinic->services()->attach($request->input('service_ids'));
        }

        $permitTypes->each(function ($permit) use ($request, $clinic) {
            $key = $permit['key'];
            $payload = $request->input("permits.$key", []);
            $file = $request->file("permits.$key.file");
            $path = $file ? $file->store('clinic-permits', 'public') : null;

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
}
