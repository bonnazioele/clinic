<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index(Request $request)
    {
    $role = $request->input('role', 'all');

        $query = User::query();

        if ($role && $role !== 'all') {
            switch ($role) {
                case 'admin':
                    $query->where('is_admin', true);
                    break;
                case 'doctor':
                    $query->where('is_doctor', true);
                    break;
                case 'secretary':
                    $query->where('is_secretary', true);
                    break;
                case 'patient':
                    $query->where('is_admin', false)
                          ->where('is_doctor', false)
                          ->where('is_secretary', false);
                    break;
            }
        }

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function ($s) use ($q) {
                $s->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('phone', 'like', "%{$q}%");
            });
        }

        switch ($role) {
            case 'doctor':
                // Use clinicsAsDoctor relation (original 'clinics' relation duplicates clinic_doctor) for clarity
                $query->with(['clinicsAsDoctor', 'services']);
                break;
            case 'secretary':
                // Only show secretaries that are still linked to at least one non-deleted clinic.
                // A clinic uses SoftDeletes, so filter out secretaries whose clinics were removed.
                $query->where('is_secretary', true)
                      ->whereHas('secretaryClinics', function ($q) {
                          $q->whereNull('clinics.deleted_at');
                      })
                      ->with(['secretaryClinics' => function ($q) {
                          $q->whereNull('clinics.deleted_at');
                      }]);
                break;
            default:
                break;
        }

        $users = $query->orderBy('name')->paginate(15)->withQueryString();

    return view('admin.users.index', compact('users', 'role'));
    }
}
