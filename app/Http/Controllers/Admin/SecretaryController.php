<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class SecretaryController extends Controller
{
    // Read-only overview
    public function index(Request $request)
    {
        $query = User::where('is_secretary', true)
            ->with('secretaryClinics:id,name');

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        if ($request->filled('clinic_id')) {
            $clinicId = (int) $request->input('clinic_id');
            $query->whereHas('secretaryClinics', fn($q) => $q->where('clinics.id', $clinicId));
        }

        $secretaries = $query->orderBy('name')->paginate(15)->withQueryString();
        return view('admin.secretaries.index', compact('secretaries'));
    }
}
