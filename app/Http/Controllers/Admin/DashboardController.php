

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Clinic;
use App\Models\ClinicType;

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

        // All clinics for the main table (with type relationship)
        $clinics = Clinic::with('type')
            ->orderByDesc('created_at')
            ->get();

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
            'clinicTypes'
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
     * Register a new clinic
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function registerClinic(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type_id' => 'nullable|exists:clinic_types,type_id',
            'branch_code' => 'required|string|max:50|unique:clinics,branch_code',
            'address' => 'required|string',
            'contact_number' => 'required|string|max:50',
            'email' => 'required|email|max:100|unique:clinics,email',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'gps_latitude' => 'nullable|numeric|between:-90,90',
            'gps_longitude' => 'nullable|numeric|between:-180,180',
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('clinic_logos', 'public');
            $validated['logo'] = $path;
        }

        // Create the clinic with approved status since it's created by admin
        $clinic = Clinic::create(array_merge($validated, [
            'status' => 'Approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Clinic registered successfully',
            'clinic' => $clinic
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

    /**
     * Add a new service
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addService(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        // Create the service
        $service = Service::create(array_merge($validated, [
            'is_active' => $request->boolean('is_active', true),
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Service added successfully',
            'service' => $service
        ]);
    }
}
