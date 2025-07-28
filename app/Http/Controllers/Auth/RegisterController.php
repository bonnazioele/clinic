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
            'name'              => ['required', 'string', 'max:255'],
            'email'             => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone'             => ['nullable', 'string', 'max:20'],
            'address'           => ['nullable', 'string'],
            'medical_document'  => ['nullable', 'file', 'mimes:pdf,doc,docx'],
            'password'          => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    protected function create(array $data)
    {
        // handle file upload if present
        if (isset($data['medical_document'])) {
            $path = request()->file('medical_document')->store('docs','public');
            $data['medical_document'] = $path;
        }

        return User::create([
            'name'              => $data['name'],
            'email'             => $data['email'],
            'phone'             => $data['phone'] ?? null,
            'address'           => $data['address'] ?? null,
            'medical_document'  => $data['medical_document'] ?? null,
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
