<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/dashboard';

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'first_name'        => ['required', 'string', 'max:255'],
            'last_name'         => ['required', 'string', 'max:255'],
            'email'             => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone'             => ['required', 'string', 'max:20'],
            'address'           => ['nullable', 'string'],
            'password'          => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    protected function create(array $data)
    {
        $fullName = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));

        return User::create([
            'name'              => $fullName,
            'email'             => $data['email'],
            'phone'             => $data['phone'] ?? null,
            'address'           => $data['address'] ?? null,
            'password'          => Hash::make($data['password']),
        ]);
    }

    protected function registered(Request $request, $user)
    {
        if ($user->is_admin) {
            return redirect()->route('admin.clinics.index');
        }

         if ($user->is_secretary) {
        return redirect()->route('secretary.appointments.index');
    }
        return redirect($this->redirectTo);
    }
}
