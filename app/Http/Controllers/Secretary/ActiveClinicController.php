<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActiveClinicController extends Controller
{
    public function update(Request $request)
    {
        $user = Auth::user();
        if (! $user || ! $user->is_secretary) {
            abort(403);
        }

        $data = $request->validate([
            'clinic_id' => 'required|integer'
        ]);

        $clinicId = (int)$data['clinic_id'];
        $assigned = $user->secretaryClinics()->where('clinics.id',$clinicId)->exists();
        if (! $assigned) {
            return back()->withErrors(['clinic_id' => 'Clinic not assigned to you.']);
        }
        $request->session()->put('active_clinic_id', $clinicId);
        return back()->with('status','Active clinic changed.');
    }
}
