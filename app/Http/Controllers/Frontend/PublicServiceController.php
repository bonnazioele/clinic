<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\Service;

class PublicServiceController extends Controller
{

    public function search(Request $request)
    {
        $q = trim((string)$request->get('q',''));
        $limit = (int)($request->get('limit', 20));
        if ($limit <= 0) { $limit = 20; }
        if ($limit > 50) { $limit = 50; }

        $query = Service::query()->select(['id','name']);
        if ($q !== '') {
            $query->where('name','like','%'.$q.'%');
        }
        $services = $query->orderBy('name')->limit($limit)->get();

        return response()->json([
            'data' => $services,
        ]);
    }
}
