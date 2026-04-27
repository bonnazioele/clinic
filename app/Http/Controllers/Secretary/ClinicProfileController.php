<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Concerns\InteractsWithClinic;
use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureSelectedClinic;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ClinicProfileController extends Controller
{
    use InteractsWithClinic;

    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\SecretaryMiddleware::class, EnsureSelectedClinic::class]);
    }

    public function edit(Request $request, Clinic $clinic)
    {
        if ((int) $clinic->id !== $this->activeClinicId($request)) {
            abort(403);
        }
        return view('secretary.clinic.edit', compact('clinic'));
    }

    public function update(Request $request, Clinic $clinic)
    {
        if ((int) $clinic->id !== $this->activeClinicId($request)) {
            abort(403);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'description' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:3072',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'queue_mode' => 'required|in:fcfs,priority',
        ]);

        if ($request->hasFile('logo')) {
            if ($clinic->logo && Storage::disk('public')->exists($clinic->logo)) {
                Storage::disk('public')->delete($clinic->logo);
            }
            $data['logo'] = $request->file('logo')->store('clinic-logos', 'public');
        }
        if ($request->hasFile('cover_image')) {
            if ($clinic->cover_image && Storage::disk('public')->exists($clinic->cover_image)) {
                Storage::disk('public')->delete($clinic->cover_image);
            }
            $data['cover_image'] = $request->file('cover_image')->store('clinic-covers', 'public');
        }

    $clinic->update($data);

        return redirect()->route('secretary.clinic.edit', $clinic)
            ->with('status', 'Clinic profile updated.');
    }
}
