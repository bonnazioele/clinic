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
            'first_name'        => ['required', 'string', 'max:50'],
            'last_name'         => ['required', 'string', 'max:50'],
            'email'             => ['required', 'string', 'email:rfc,dns', 'max:100', 'unique:users,email'],
            'phone'             => ['required', 'string', 'max:11', 'regex:/^([0-9]{10,11})$/'],
            'address'           => ['nullable', 'string'],
            'age'               => ['nullable', 'integer', 'min:0', 'max:120'],
            'birthdate'         => ['required', 'date'],
            'password'          => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'phone.regex' => 'Please enter a valid phone number (10-11 digits, numbers only).',
            'phone.max' => 'Phone number must not exceed 11 digits.',
            'email.email' => 'Please provide a valid email address.',
            'email.max' => 'Email address cannot exceed 100 characters.',
            'email.unique' => 'This email address is already registered.',
            'password.confirmed' => 'Passwords do not match.',
        ]);
    }

    protected function create(array $data)
    {
        try {
            $user = User::create([
                'first_name'        => $data['first_name'],
                'last_name'         => $data['last_name'],
                'email'             => $data['email'],
                'phone'             => $data['phone'] ?? null,
                'address'           => $data['address'] ?? null,
                'age'               => $data['age'] ?? null,
                'birthdate'         => $data['birthdate'] ?? null,
                'is_active'         => true,
                'is_system_admin'   => false,
                'password'          => Hash::make($data['password']),
            ]);
            return $user;
        } catch (\Exception $e) {
            \Log::error('Registration error: '.$e->getMessage());
            throw ValidationException::withMessages([
                'general' => 'Could not complete registration. Please try again.'
            ]);
        }
    }

    protected function registered(Request $request, $user)
    {
        if ($user->is_admin) {
            return redirect()->route('admin.clinics.index');
        }

         if ($user->is_secretary) {
            return redirect()->route('secretary.appointments.index');
        }
        return redirect($this->redirectTo)->with('success', 'Account created successfully.');
    }

}
