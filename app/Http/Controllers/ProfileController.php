<?php

namespace App\Http\Controllers;

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
       
        return view('profile.show', [
            'user' => Auth::user()
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

    public function storeHistory(Request $request)
{
    $request->validate([
        'condition'       => 'required|string|max:255',
        'diagnosis_date'  => 'nullable|date',
        'notes'           => 'nullable|string',
    ]);

    Auth::user()->patientHistories()->create($request->all());

    return back()->with('status', 'Medical history added successfully.');
}
}
