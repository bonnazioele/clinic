<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $clinic = $request->attributes->get('ownerClinic');

        $doctors = $clinic->doctors()->get();
        $secretaries = $clinic->secretaries()->get();

        // Potential candidates not already attached
        $doctorCandidates = User::where('is_doctor', true)
            ->whereDoesntHave('clinicsAsDoctor', function($q) use ($clinic){
                $q->where('clinics.id', $clinic->id);
            })->orderBy('name')->limit(20)->get();

        $secretaryCandidates = User::where('is_secretary', true)
            ->whereDoesntHave('clinicsAsSecretary', function($q) use ($clinic){
                $q->where('clinics.id', $clinic->id);
            })->orderBy('name')->limit(20)->get();

        return view('owner.staff.index', compact('clinic','doctors','secretaries','doctorCandidates','secretaryCandidates'));
    }

    public function attach(Request $request)
    {
        $clinic = $request->attributes->get('ownerClinic');
        $request->validate([
            'role' => 'required|in:doctor,secretary',
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->input('user_id'));
        if ($request->input('role') === 'doctor' && $user->is_doctor) {
            $clinic->doctors()->syncWithoutDetaching([$user->id]);
            return back()->with('success', 'Doctor attached to clinic.');
        }
        if ($request->input('role') === 'secretary' && $user->is_secretary) {
            $clinic->secretaries()->syncWithoutDetaching([$user->id]);
            return back()->with('success', 'Secretary attached to clinic.');
        }
        return back()->with('error', 'Invalid staff selection.');
    }

    public function detach(Request $request)
    {
        $clinic = $request->attributes->get('ownerClinic');
        $request->validate([
            'role' => 'required|in:doctor,secretary',
            'user_id' => 'required|exists:users,id',
        ]);
        $userId = (int) $request->input('user_id');

        if ($request->input('role') === 'doctor') {
            $clinic->doctors()->detach($userId);
            return back()->with('success', 'Doctor detached from clinic.');
        }
        if ($request->input('role') === 'secretary') {
            $clinic->secretaries()->detach($userId);
            return back()->with('success', 'Secretary detached from clinic.');
        }
        return back()->with('error', 'Invalid request.');
    }
}
