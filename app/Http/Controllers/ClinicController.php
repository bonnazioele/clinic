<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use Illuminate\Http\Request;
use App\Models\Service;

class ClinicController extends Controller
{
    public function index(Request $req)
    {
        $query = Clinic::with('services')->where('status', 'Approved');

        if($req->service_id) {
            $query->whereHas('services', fn($q)=> $q->where('services.id',$req->service_id));
        }
        if($req->name) {
            $query->where('name','like','%'.$req->name.'%')
                  ->orWhere('address','like','%'.$req->name.'%');
        }

        $clinics = $query->paginate(10);
        
        // Load only active services for the filter dropdown
        $services = Service::active()->orderBy('service_name')->get();

        return view('clinics.index', compact('clinics', 'services'));
    }
}

