<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Clinic;
use App\Models\Service;

class DoctorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        // ensure only secretaries hit these routes:
        $this->middleware(function($req, $next) {
            if (! $req->user()?->is_secretary) {
                abort(403,'Forbidden');
            }
            return $next($req);
        });
    }

    /** GET /secretary/doctors */
    public function index()
    {
        $doctors = User::where('is_doctor', true)
                       ->orderBy('name')
                       ->paginate(15);

        return view('secretary.doctors.index', compact('doctors'));
    }

    /** GET /secretary/doctors/create */
    public function create()
{
    $clinics  = Clinic::all();
    $services = Service::all();
    return view('secretary.doctors.create', compact('clinics','services'));
}


    /** POST /secretary/doctors */
    public function store(Request $req)
{
    $data = $req->validate([
        'name'             => 'required|string|max:255',
        'email'            => 'required|email|unique:users,email',
        'password'         => 'required|string|min:6|confirmed',
        'clinic_ids'       => 'array',
        'clinic_ids.*'     => 'exists:clinics,id',
        'service_ids'      => 'array',
        'service_ids.*'    => 'exists:services,id',
        'phone'            => 'nullable|string|max:50',
        'address'          => 'nullable|string|max:500',
    ]);

    $doctor = User::create([
        'name'       => $data['name'],
        'email'      => $data['email'],
        'password'   => Hash::make($data['password']),
        'phone'      => $data['phone'] ?? null,
        'address'    => $data['address'] ?? null,
        'is_doctor'  => true,
    ]);

    // sync pivots:
    $doctor->clinics()->sync($data['clinic_ids'] ?? []);
    $doctor->services()->sync($data['service_ids'] ?? []);

    return redirect()->route('secretary.doctors.index')
                     ->with('status','Doctor added.');
}

    /** GET /secretary/doctors/{doctor}/edit */
    public function edit(User $doctor)
{
    abort_unless($doctor->is_doctor,404);
    $clinics  = Clinic::all();
    $services = Service::all();
    return view('secretary.doctors.edit', compact('doctor','clinics','services'));
}

    /** PATCH /secretary/doctors/{doctor} */
    public function update(Request $req, User $doctor)
{
    abort_unless($doctor->is_doctor,404);

    $data = $req->validate([
        'name'             => 'required|string|max:255',
        'email'            => 'required|email|unique:users,email,'.$doctor->id,
        'password'         => 'nullable|string|min:6|confirmed',
        'clinic_ids'       => 'array',
        'clinic_ids.*'     => 'exists:clinics,id',
        'service_ids'      => 'array',
        'service_ids.*'    => 'exists:services,id',
        'phone'            => 'nullable|string|max:50',
        'address'          => 'nullable|string|max:500',
    ]);

    $doctor->update([
        'name'     => $data['name'],
        'email'    => $data['email'],
        'phone'    => $data['phone'] ?? null,
        'address'  => $data['address'] ?? null,
        'password' => $data['password']
                        ? Hash::make($data['password'])
                        : $doctor->password,
    ]);

    $doctor->clinics()->sync($data['clinic_ids'] ?? []);
    $doctor->services()->sync($data['service_ids'] ?? []);

    return back()->with('status','Doctor updated.');
}

    /** DELETE /secretary/doctors/{doctor} */
    public function destroy(User $doctor)
    {
        abort_unless($doctor->is_doctor, 404);
        $doctor->delete();
        return back()->with('status','Doctor removed.');
    }
}
