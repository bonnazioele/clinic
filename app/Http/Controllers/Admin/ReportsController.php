<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    /**
     * Display the reports landing page (placeholder for future analytics).
     */
    public function index(Request $request)
    {
        // In future: gather KPIs, charts, aggregated metrics, filters, export options.
        return view('admin.reports.index');
    }
}
