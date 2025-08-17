<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Clinic;

class SecretaryController extends Controller
{
    public function index()
    {
        $secretaries = User::where('is_secretary', true)
            ->with('secretaryClinics:id,name')
            ->orderBy('name')
            ->paginate(15);
        return view('admin.secretaries.index', compact('secretaries'));
    }

    public function create()
    {
        $clinics = Clinic::orderBy('name')->get(['id','name']);
        return view('admin.secretaries.create', compact('clinics'));
    }

    public function store(Request $req)
    {
        $data = $req->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'clinic_ids' => 'nullable|array',
            'clinic_ids.*' => 'integer|exists:clinics,id',
        ]);

        $secretary = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'is_secretary' => true,
            'is_admin' => false,
            'is_doctor' => false,
        ]);

        $secretary->secretaryClinics()->sync($data['clinic_ids'] ?? []);

        return redirect()->route('admin.secretaries.index')
            ->with('success', 'Secretary created.');
    }

    public function edit(User $secretary)
    {
        abort_unless($secretary->is_secretary, 404);
        $clinics = Clinic::orderBy('name')->get(['id','name']);
        $selected = $secretary->secretaryClinics()->pluck('clinics.id')->all();
        return view('admin.secretaries.edit', compact('secretary','clinics','selected'));
    }

    public function update(Request $req, User $secretary)
    {
        abort_unless($secretary->is_secretary, 404);
        $data = $req->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $secretary->id,
            'password' => 'nullable|string|min:6|confirmed',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'clinic_ids' => 'nullable|array',
            'clinic_ids.*' => 'integer|exists:clinics,id',
        ]);

        $secretary->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            // Only set password if provided
            'password' => $data['password'] ?? $secretary->password,
        ]);

        $secretary->secretaryClinics()->sync($data['clinic_ids'] ?? []);

        return redirect()->route('admin.secretaries.index')
            ->with('success', 'Secretary updated.');
    }

    public function destroy(User $secretary)
    {
        abort_unless($secretary->is_secretary, 404);
        $secretary->secretaryClinics()->detach();
        $secretary->delete();
        return redirect()->route('admin.secretaries.index')
            ->with('success', 'Secretary deleted.');
    }
}
