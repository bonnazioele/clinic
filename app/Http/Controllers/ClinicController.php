<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ClinicController extends Controller
{
    // =========================================================================
    // PUBLIC LISTING
    // =========================================================================

    public function index(Request $req)
    {
        $query = Clinic::with('services')
            ->whereIn('status', ['approved', 'active']);

        if ($req->filled('service_id')) {
            $query->whereHas('services', fn ($q) => $q->where('services.id', $req->service_id));
        }

        if ($req->filled('name')) {
            $name = trim($req->name);
            $query->where(function ($q) use ($name) {
                $q->where('name', 'like', '%' . $name . '%')
                  ->orWhere('address', 'like', '%' . $name . '%');
            });
        }

        // location keyword search
        if ($req->filled('location')) {
            $location = trim($req->location);
            $query->where(function ($q) use ($location) {
                $q->where('name', 'like', '%' . $location . '%')
                  ->orWhere('address', 'like', '%' . $location . '%');
            });
        }

        // search nearby clinics using lat/lng
        if ($req->filled('lat') && $req->filled('lng')) {
            $lat = (float) $req->lat;
            $lng = (float) $req->lng;
            $radiusKm = $req->filled('radius') ? (float) $req->radius : 10;

            $distanceSql = "(6371 * acos(
                cos(radians(?)) * cos(radians(gps_latitude))
                * cos(radians(gps_longitude) - radians(?))
                + sin(radians(?)) * sin(radians(gps_latitude))
            ))";

            $query->whereNotNull('gps_latitude')
                ->whereNotNull('gps_longitude')
                ->select('clinics.*')
                ->selectRaw($distanceSql . ' as distance_km', [$lat, $lng, $lat])
                ->having('distance_km', '<=', $radiusKm)
                ->orderBy('distance_km');
        } else {
            $query->latest('id');
        }

        $clinics = $query->paginate(10)->withQueryString();

        return view('clinics.index', compact('clinics'));
    }

    // =========================================================================
    // REGISTRATION
    // =========================================================================

    public function create()
    {
        $services = Service::orderBy('name')->get();

        return view('clinics.create', compact('services'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                 => 'required|string|max:255',
            'branch_code'          => 'nullable|string|max:255',
            'address'              => 'required|string|max:500',
            'gps_latitude'         => 'nullable|numeric|between:-90,90',
            'gps_longitude'        => 'nullable|numeric|between:-180,180',
            'contact_number'       => 'nullable|string|max:255',
            'email'                => 'nullable|email|max:255',
            'contact_first_name'   => 'nullable|string|max:255',
            'contact_last_name'    => 'nullable|string|max:255',
            'contact_person_email' => 'nullable|email|max:255',
            'description'          => 'nullable|string',
            'queue_mode'           => 'nullable|string|in:fcfs,appointment',
            'logo'                 => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'cover_image'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'services'             => 'nullable|array',
            'services.*'           => 'integer|exists:services,id',
        ]);

        // auto-geocode from address if lat/lng not provided
        if (empty($validated['gps_latitude']) || empty($validated['gps_longitude'])) {
            $coords = $this->geocodeAddress($validated['address']);

            if ($coords) {
                $validated['gps_latitude'] = $coords['lat'];
                $validated['gps_longitude'] = $coords['lng'];
            }
        }

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('clinics/logos', 'public');
        }

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('clinics/covers', 'public');
        }

        $validated['created_by_user_id'] = Auth::id();
        $validated['status'] = 'pending';

        $services = $validated['services'] ?? [];
        unset($validated['services']);

        $clinic = Clinic::create($validated);

        if ($services) {
            $clinic->services()->sync($services);
        }

        return redirect()
            ->route('clinics.index')
            ->with('success', 'Clinic registered successfully. It will be visible once approved.');
    }

    // =========================================================================
    // SHOW
    // =========================================================================

    public function show(Request $request, Clinic $clinic)
    {
        if (! $clinic->isApprovedLike()) {
            abort(404);
        }

        $clinic->load(['services:id,name', 'doctors:id,first_name,last_name']);

        $user = Auth::user();
        $canEdit = false;

        if ($user) {
            $canEdit = $user->is_secretary
                && $clinic->secretaries()->where('clinic_secretary.secretary_id', $user->id)->exists();
        }

        return response()->json([
            'id'              => $clinic->id,
            'name'            => $clinic->name,
            'address'         => $clinic->address,
            'gps_latitude'    => $clinic->gps_latitude,
            'gps_longitude'   => $clinic->gps_longitude,
            'contact_number'  => $clinic->contact_number,
            'email'           => $clinic->email,
            'description'     => $clinic->description,
            'logo_url'        => $clinic->logo ? asset('storage/' . $clinic->logo) : null,
            'cover_image_url' => $clinic->cover_image ? asset('storage/' . $clinic->cover_image) : null,
            'services'        => $clinic->services->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
            ]),
            'doctors'         => $clinic->doctors->map(fn ($d) => [
                'id'   => $d->id,
                'name' => trim($d->first_name . ' ' . $d->last_name),
            ]),
            'can_edit' => $canEdit,
            'edit_url' => $canEdit ? route('secretary.clinic.edit', $clinic) : null,
        ]);
    }

    // =========================================================================
    // EDIT / UPDATE
    // =========================================================================

    public function edit(Clinic $clinic)
    {
        $this->authorizeSecretary($clinic);

        $services = Service::orderBy('name')->get();
        $selectedServices = $clinic->services->pluck('id')->toArray();

        return view('clinics.edit', compact('clinic', 'services', 'selectedServices'));
    }

    public function update(Request $request, Clinic $clinic)
    {
        $this->authorizeSecretary($clinic);

        $validated = $request->validate([
            'name'                 => 'required|string|max:255',
            'branch_code'          => 'nullable|string|max:255',
            'address'              => 'required|string|max:500',
            'gps_latitude'         => 'nullable|numeric|between:-90,90',
            'gps_longitude'        => 'nullable|numeric|between:-180,180',
            'contact_number'       => 'nullable|string|max:255',
            'email'                => 'nullable|email|max:255',
            'contact_first_name'   => 'nullable|string|max:255',
            'contact_last_name'    => 'nullable|string|max:255',
            'contact_person_email' => 'nullable|email|max:255',
            'description'          => 'nullable|string',
            'queue_mode'           => 'nullable|string|in:fcfs,appointment',
            'logo'                 => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'cover_image'          => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'services'             => 'nullable|array',
            'services.*'           => 'integer|exists:services,id',
        ]);

        if (empty($validated['gps_latitude']) || empty($validated['gps_longitude'])) {
            $coords = $this->geocodeAddress($validated['address']);

            if ($coords) {
                $validated['gps_latitude'] = $coords['lat'];
                $validated['gps_longitude'] = $coords['lng'];
            }
        }

        if ($request->hasFile('logo')) {
            if ($clinic->logo) {
                Storage::disk('public')->delete($clinic->logo);
            }
            $validated['logo'] = $request->file('logo')->store('clinics/logos', 'public');
        }

        if ($request->hasFile('cover_image')) {
            if ($clinic->cover_image) {
                Storage::disk('public')->delete($clinic->cover_image);
            }
            $validated['cover_image'] = $request->file('cover_image')->store('clinics/covers', 'public');
        }

        $services = $validated['services'] ?? [];
        unset($validated['services']);

        $clinic->update($validated);
        $clinic->services()->sync($services);

        return redirect()
            ->route('secretary.clinic.edit', $clinic)
            ->with('success', 'Clinic updated successfully.');
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    private function geocodeAddress(string $address): ?array
    {
        $address = trim($address);
        if ($address === '') {
            return null;
        }

        $response = Http::timeout(10)
            ->withHeaders([
                'User-Agent' => config('app.name', 'Laravel') . '/1.0 clinic geocoder',
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
            return null;
        }

        $first = $response->json()[0] ?? null;

        if (! $first || !isset($first['lat'], $first['lon'])) {
            return null;
        }

        return [
            'lat' => round((float) $first['lat'], 7),
            'lng' => round((float) $first['lon'], 7),
        ];
    }

    private function authorizeSecretary(Clinic $clinic): void
    {
        $user = Auth::user();

        abort_unless(
            $user && $user->is_secretary
            && $clinic->secretaries()->where('clinic_secretary.secretary_id', $user->id)->exists(),
            403,
            'You are not authorized to manage this clinic.'
        );
    }
}