<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClinicController extends Controller
{

    public function index(Request $request)
    {
        $query = Clinic::with(['services']);

        if ($request->filled('name')) {
            $name = trim((string) $request->input('name'));
            $query->where('name', 'like', "%{$name}%");
        }
        if ($request->filled('status')) {
            $query->where('status', (string) $request->input('status'));
        }

        $clinics = $query->latest()->paginate(10)->withQueryString();

        $mapQuery = Clinic::query();
        if ($request->filled('name')) {
            $name = trim((string) $request->input('name'));
            $mapQuery->where('name', 'like', "%{$name}%");
        }
        if ($request->filled('status')) {
            $mapQuery->where('status', (string) $request->input('status'));
        }
        $clinicsWithCoords = $mapQuery
            ->whereNotNull('gps_latitude')
            ->whereNotNull('gps_longitude')
            ->get(['id','name','address','gps_latitude','gps_longitude']);

        return view('admin.clinics.index', [
            'clinics' => $clinics,
            'clinicsWithCoords' => $clinicsWithCoords,
        ]);
    }

    public function create()
    {
        $services = Service::all();
        return view('admin.clinics.create', compact('services'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'address'        => 'required|string',
            'latitude'       => 'required|numeric|between:-90,90',
            'longitude'      => 'required|numeric|between:-180,180',
            'branch_code'    => 'required|string|max:255|unique:clinics,branch_code',
            'contact_number' => 'required|string|max:50',
            'email'          => 'required|email|max:255|unique:clinics,email',
            'logo'           => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'service_ids'    => 'nullable|array',
            'service_ids.*'  => 'exists:services,id',
            'secretary_name'                  => 'required|string|max:255',
            'secretary_email'                 => 'required|email|max:255|unique:users,email',
            'secretary_phone'                 => 'nullable|string|max:50',
            'secretary_password'              => 'required|string|min:8|confirmed',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('clinic-logos', 'public');
        }

        \DB::transaction(function () use ($data, $logoPath, &$clinic) {
            $clinic = Clinic::create([
                'name'           => $data['name'],
                'address'        => $data['address'],
                'gps_latitude'   => $data['latitude'],
                'gps_longitude'  => $data['longitude'],
                'branch_code'    => $data['branch_code'],
                'contact_number' => $data['contact_number'],
                'email'          => $data['email'],
                'logo'           => $logoPath,
            ]);

            $clinic->services()->sync($data['service_ids'] ?? []);

            $secretary = \App\Models\User::create([
                'name'        => $data['secretary_name'],
                'email'       => $data['secretary_email'],
                'phone'       => $data['secretary_phone'] ?? null,
                'password'    => $data['secretary_password'],
                'is_secretary'=> true,
            ]);

            $clinic->secretaries()->syncWithoutDetaching([$secretary->id]);
        });

        return redirect()
            ->route('admin.clinics.index')
            ->with('status','Clinic added successfully. Secretary account created: ' . $data['secretary_email']);
    }

    public function show(Clinic $clinic)
    {
        $clinic->load(['services', 'secretaries:id,name,email,phone', 'doctors:id,name,email,phone']);

        $todayAppointments = $clinic->appointments()
            ->whereDate('appointment_date', today())
            ->count();
        $totalAppointments = $clinic->appointments()->count();
        $waitingCount = $clinic->queueEntries()->where('status','waiting')->count();
        $servedToday = $clinic->queueEntries()->where('status','served')
            ->whereDate('served_at', today())->count();

        return view('admin.clinics.show', [
            'clinic' => $clinic,
            'todayAppointments' => $todayAppointments,
            'totalAppointments' => $totalAppointments,
            'waitingCount' => $waitingCount,
            'servedToday' => $servedToday,
        ]);
    }

    public function edit(Clinic $clinic)
    {
        $services = Service::all();
        return view('admin.clinics.edit', compact('clinic','services'));
    }

    public function update(Request $request, Clinic $clinic)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'address'        => 'required|string',
            'latitude'       => 'required|numeric|between:-90,90',
            'longitude'      => 'required|numeric|between:-180,180',
            'branch_code'    => 'required|string|max:255|unique:clinics,branch_code,' . $clinic->id,
            'contact_number' => 'required|string|max:50',
            'email'          => 'required|email|max:255|unique:clinics,email,' . $clinic->id,
            'logo'           => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'service_ids'    => 'nullable|array',
            'service_ids.*'  => 'exists:services,id',
        ]);


        if ($request->hasFile('logo')) {

            if ($clinic->logo && \Storage::disk('public')->exists($clinic->logo)) {
                \Storage::disk('public')->delete($clinic->logo);
            }
            $data['logo'] = $request->file('logo')->store('clinic-logos', 'public');
        }

        $clinic->update([
            'name'           => $data['name'],
            'address'        => $data['address'],
            'gps_latitude'   => $data['latitude'],
            'gps_longitude'  => $data['longitude'],
            'branch_code'    => $data['branch_code'],
            'contact_number' => $data['contact_number'],
            'email'          => $data['email'],
            'logo'           => $data['logo'] ?? $clinic->logo,
        ]);


        $clinic->services()->sync($data['service_ids'] ?? []);

        return redirect()->route('admin.clinics.index')->with('status', 'Clinic updated successfully.');
    }

    public function destroy(Clinic $clinic)
    {
        $clinic->delete();
        return back()->with('status','Clinic removed.');
    }

    public function approve(Clinic $clinic)
    {
        $clinic->update(['status' => 'approved']);
        return back()->with('status', 'Clinic approved.');
    }

    public function decline(Clinic $clinic)
    {
        $clinic->update(['status' => 'declined']);
        return back()->with('status', 'Clinic declined.');
    }

}
