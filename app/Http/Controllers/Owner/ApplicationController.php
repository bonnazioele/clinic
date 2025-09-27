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
        return view('owner.apply', compact('services'));
    }

    public function store(Request $request)
    {
        $data = $request->all();
        Validator::make($data, [
            'clinic_name' => ['required','string','max:255'],
            'clinic_address' => ['required','string','max:1024'],
            'clinic_contact' => ['required','string','max:50'],
            'branch_code'   => ['required','string','max:255', Rule::unique('clinics','branch_code')->whereNull('deleted_at')],
            'clinic_email'  => ['required','email','max:255', Rule::unique('clinics','email')->whereNull('deleted_at')],
            'latitude'      => ['nullable','numeric','between:-90,90'],
            'longitude'     => ['nullable','numeric','between:-180,180'],
            'logo'          => ['nullable','image','mimes:jpeg,png,jpg,gif,svg','max:2048'],
            'service_ids'   => ['nullable','array'],
            'service_ids.*' => ['integer','exists:services,id'],
        ])->validate();

        $branchCode = $data['branch_code'];
        $clinicEmail = $data['clinic_email'];

        $logoPath = null;
        if (request()->hasFile('logo')) {
            $logoPath = request()->file('logo')->store('clinic-logos', 'public');
        }

        $clinic = Clinic::create([
            'user_id' => null,
            'name' => $data['clinic_name'],
            'address' => $data['clinic_address'],
            'contact_number' => $data['clinic_contact'],
            'description' => null,
            'email' => $clinicEmail,
            'owner_first_name' => null,
            'owner_last_name' => null,
            'branch_code' => $branchCode,
            'logo' => $logoPath,
            'gps_latitude' => $data['latitude'] ?? null,
            'gps_longitude' => $data['longitude'] ?? null,
            'status' => 'pending',
        ]);

        if ($request->filled('service_ids')) {
            $clinic->services()->attach($request->input('service_ids'));
        }

        return redirect()->route('owner.apply.thanks')
            ->with('success', 'Application submitted! We\'ve emailed you a confirmation. You will receive credentials once your clinic is approved.');
    }
}
