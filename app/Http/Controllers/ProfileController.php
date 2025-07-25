<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', ['user'=>Auth::user()]);
    }

    public function update(Request $req)
    {
        $u = Auth::user();
        $data = $req->validate([
            'name'=>'required',
            'phone'=>'nullable',
            'address'=>'nullable',
            'medical_document'=>'nullable|file|mimes:pdf,doc,docx',
        ]);

        if($req->hasFile('medical_document')){
            $path = $req->file('medical_document')->store('docs');
            $data['medical_document'] = $path;
        }

        $u->update($data);
        return back()->with('status','Profile updated');
    }
}

