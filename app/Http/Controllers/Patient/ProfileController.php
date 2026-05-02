<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show()
{
    $user = Auth::user();

    $reportMonths = $user->appointments()
        ->where('status', 'completed')
        ->selectRaw('DATE_FORMAT(appointment_date, "%Y-%m") as month')
        ->distinct()
        ->orderByDesc('month')
        ->pluck('month');

    return view('profile.show', [
        'user'         => $user,
    ]);
}

    public function edit()
    {
        return view('profile.edit', [
            'user' => Auth::user()
        ]);
    }

    public function update(Request $req)
    {
        $u = Auth::user();

        $data = $req->validate([
            'name'             => 'required|string|max:255',
            'phone'            => 'nullable|string|max:50',
            'address'          => 'nullable|string',
            'medical_document' => 'nullable|file|mimes:pdf,doc,docx',
        ]);

        if ($req->hasFile('medical_document')) {
            $path = $req->file('medical_document')->store('docs');
            $data['medical_document'] = $path;
        }

        $u->update($data);

        return back()->with('status','Profile updated');
    }


}
