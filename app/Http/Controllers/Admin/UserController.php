<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index(Request $request)
    {
    $role = $request->input('role', 'all'); // default to all

        $query = User::query();

        // Role filter
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

        // Search by name/email/phone
        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function ($s) use ($q) {
                $s->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%")
                  ->orWhere('phone', 'like', "%{$q}%");
            });
        }

        // Eager load relations needed per role for the table view
        switch ($role) {
            case 'doctor':
                // Doctors: show clinics and services
                $query->with(['clinics', 'services']);
                break;
            case 'secretary':
                // Secretaries: show assigned clinics
                $query->with(['secretaryClinics']);
                break;
            default:
                // Patients/Admins/All: no heavy eager loads needed
                break;
        }

        $users = $query->orderBy('name')->paginate(15)->withQueryString();

    return view('admin.users.index', compact('users', 'role'));
    }
}
