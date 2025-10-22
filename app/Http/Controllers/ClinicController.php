<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClinicController extends Controller
{
    public function index(Request $req)
    {
        $query = Clinic::with('services')
            ->whereIn('status', ['approved','active']);

        if($req->service_id) {
            $query->whereHas('services', fn($q)=> $q->where('service_id',$req->service_id));
        }
        if($req->name) {
            $query->where('name','like','%'.$req->name.'%');
        }

    $clinics = $query->paginate(10);

    return view('clinics.index', compact('clinics'));
    }

    public function show(Request $request, Clinic $clinic)
    {
        
        if(! $clinic->isApprovedLike()) {
            abort(404);
        }

        $clinic->load(['services:id,name','doctors:id,first_name,last_name,name']);

        $user = Auth::user();
        $canEdit = false;
        if($user) {
            
            $canEdit = $user->is_secretary && $clinic->secretaries()->where('users.id', $user->id)->exists();
        }

        return response()->json([
            'id' => $clinic->id,
            'name' => $clinic->name,
            'address' => $clinic->address,
            'contact_number' => $clinic->contact_number,
            'email' => $clinic->email,
            'description' => $clinic->description,
            'logo_url' => $clinic->logo ? asset('storage/'.$clinic->logo) : null,
            'cover_image_url' => $clinic->cover_image ? asset('storage/'.$clinic->cover_image) : null,
            'services' => $clinic->services->map(fn($s)=> ['id'=>$s->id,'name'=>$s->name]),
            'doctors' => $clinic->doctors->map(fn($d)=> [
                'id'=>$d->id,
                'name'=>$d->name,
            ]),
            'can_edit' => $canEdit,
            'edit_url' => $canEdit ? route('secretary.clinic.edit', $clinic) : null,
        ]);
    }
}

