<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Clinic;
use App\Models\ClinicType;
use App\Models\Service;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * Display the system admin dashboard
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        // Use Clinic model scopes for status counts
        $totalClinics = Clinic::count();
        $approvedClinics = Clinic::status('Approved')->count();
        $pendingClinics = Clinic::status('Pending')->count();
        $rejectedClinics = Clinic::status('Rejected')->count();

        $totalServices = Service::count();
        $activeServices = Service::active()->count();
        $totalClinicTypes = ClinicType::count();

        // Recent approvals and rejections (with relationships)
        $recentApprovals = Clinic::with(['approvedBy', 'type'])
            ->status('Approved')
            ->orderByDesc('approved_at')
            ->take(5)
            ->get();

        $recentRejections = Clinic::with(['rejectedBy', 'type'])
            ->status('Rejected')
            ->orderByDesc('rejected_at')
            ->take(5)
            ->get();

        // All clinics for the main table (with type relationship and user who submitted)
        $clinics = Clinic::with(['type', 'user'])
            ->orderByDesc('created_at')
            ->paginate(12);

        // Get clinic types for the add clinic modal
        $clinicTypes = ClinicType::all();

        return view('admin.dashboard', compact(
            'totalClinics',
            'approvedClinics',
            'pendingClinics',
            'rejectedClinics',
            'recentApprovals',
            'recentRejections',
            'clinics',
            'clinicTypes',
            'totalServices',
            'activeServices',
            'totalClinicTypes'
        ));
    }

    /**
     * Approve a clinic registration
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function approveClinic(Request $request, $id)
    {
        $clinic = Clinic::findOrFail($id);

        // Validate the clinic is in pending status
        if ($clinic->status !== 'Pending') {
            return response()->json([
                'success' => false,
                'message' => 'Clinic is not in pending status'
            ], 400);
        }

        // Update clinic status
        $clinic->update([
            'status' => 'Approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
            'rejected_at' => null,
            'rejected_by' => null,
            'rejection_reason' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Clinic approved successfully',
        ]);
    }

    /**
     * Log out the admin user and redirect to login page
     */
    public function logout(Request $request)
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
    public function rejectClinic(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|min:10|max:500'
        ]);

        $clinic = Clinic::findOrFail($id);

        // Validate the clinic is in pending status
        if ($clinic->status !== 'Pending') {
            return response()->json([
                'success' => false,
                'message' => 'Clinic is not in pending status'
            ], 400);
        }

        // Update clinic status
        $clinic->update([
            'status' => 'Rejected',
            'rejected_at' => now(),
            'rejected_by' => auth()->id(),
            'rejection_reason' => $request->rejection_reason,
            'approved_at' => null,
            'approved_by' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Clinic rejected successfully',
            'clinic' => $clinic->fresh()
        ]);
    }

    /**
     * Add a new system admin
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addAdmin(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        // Create the admin user
        $admin = User::create(array_merge($validated, [
            'password' => bcrypt($validated['password']),
            'role' => 'admin',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Admin added successfully',
            'admin' => $admin
        ]);
    }
}
