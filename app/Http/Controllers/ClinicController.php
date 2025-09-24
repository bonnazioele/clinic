<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Appointment;
use Illuminate\Http\Request;

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
}

