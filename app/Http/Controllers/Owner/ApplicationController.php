<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ApplicationController extends Controller
{
    public function create()
    {
        $services = \App\Models\Service::orderBy('name')->get(['id','name']);
        return view('owner.apply', compact('services'));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        Validator::make($data, [
            'clinic_name' => ['required','string','max:255'],
            'clinic_address' => ['required','string','max:1024'],
            'clinic_contact' => ['required','string','max:50'],
            'branch_code'   => ['required','string','max:255','unique:clinics,branch_code'],
            'clinic_email'  => ['required','email','max:255','unique:clinics,email'],
            'latitude'      => ['nullable','numeric','between:-90,90'],
            'longitude'     => ['nullable','numeric','between:-180,180'],
            'logo'          => ['nullable','image','mimes:jpeg,png,jpg,gif,svg','max:2048'],
            'service_ids'   => ['nullable','array'],
            'service_ids.*' => ['integer','exists:services,id'],
            'first_name' => ['required','string','max:255'],
            'last_name' => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:users,email'],
            'password' => ['required','string','min:8','confirmed'],
        ])->validate();

        // Create applicant as secretary directly (owner role deprecated)
        $user = User::create([
            'first_name'    => $data['first_name'],
            'last_name'     => $data['last_name'],
            'name'          => $data['first_name'].' '.$data['last_name'],
            'email'         => $data['email'],
            'password'      => Hash::make($data['password']),
            'is_secretary'  => false, // will be promoted when clinic approved
        ]);

        // Branch code and clinic email are required by validation; use as-is
        $branchCode = $data['branch_code'];
        $clinicEmail = $data['clinic_email'];

        // Handle optional logo upload
        $logoPath = null;
        if (request()->hasFile('logo')) {
            $logoPath = request()->file('logo')->store('clinic-logos', 'public');
        }

        $clinic = Clinic::create([
            'user_id' => $user->id,
            'name' => $data['clinic_name'],
            'address' => $data['clinic_address'],
            'contact_number' => $data['clinic_contact'],
            'description' => null,
            'email' => $clinicEmail,
            'branch_code' => $branchCode,
            'logo' => $logoPath,
            'gps_latitude' => $data['latitude'] ?? null,
            'gps_longitude' => $data['longitude'] ?? null,
            'status' => 'pending',
        ]);

        // Attach selected services if any
        if (!empty($data['service_ids']) && is_array($data['service_ids'])) {
            $clinic->services()->sync($data['service_ids']);
        }

        return redirect()->route('owner.apply.thanks')
            ->with('success', 'Application submitted! We\'ll email you once your clinic is approved.')
            ->with('owner_login_email', $user->email);
    }
}
