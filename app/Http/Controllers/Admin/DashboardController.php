<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Middleware\AdminMiddleware;
use App\Models\Clinic;
use App\Models\Service;
use App\Models\QueueEntry;
use App\Models\User;
use App\Models\Appointment;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', AdminMiddleware::class]);
    }

    public function index()
    {
        $today = now()->toDateString();
        $startOfMonth = now()->startOfMonth();
        $startOfWeek = now()->startOfWeek();

        $totalClinics = Clinic::whereIn('status', ['approved', 'active'])->count();
        $activeClinics = Clinic::whereIn('status', ['approved', 'active'])->count();
        $pendingClinicsCount = Clinic::where('status', 'pending')->count();
        $rejectedClinics = Clinic::where('status', 'rejected')->count();
        $newClinicsThisMonth = Clinic::whereIn('status', ['approved', 'active'])
            ->where('created_at', '>=', $startOfMonth)
            ->count();

        $totalServices = Service::count();
        $serviceBreakdown = $this->serviceBreakdown();

        $totalUsers = User::count();
        $newUsersThisWeek = User::where('created_at', '>=', $startOfWeek)->count();

        $patientsInQueueNow = QueueEntry::whereIn('status', QueueEntry::activePatientStatuses())->count();
        $appointmentsToday = Appointment::whereDate('appointment_date', $today)->count();
        $cancelledToday = Appointment::whereDate('appointment_date', $today)
            ->where('status', 'cancelled')
            ->count();

        $pendingClinics = Clinic::where('status', 'pending')
            ->latest()
            ->take(6)
            ->get(['id', 'name', 'address', 'email', 'contact_number', 'created_at']);

        $topClinicsByQueue = Clinic::query()
            ->select('clinics.id', 'clinics.name')
            ->withCount([
                'queueEntries as queue_count' => fn ($query) => $query->forDashboardDay($today),
            ])
            ->whereIn('status', ['approved', 'active'])
            ->orderByDesc('queue_count')
            ->orderBy('name')
            ->take(5)
            ->get();

        $registrationTrend = $this->registrationTrend();
        [$sparklinePoints, $sparklineFillPath] = $this->sparklinePaths($registrationTrend);
        $recentActivity = $this->recentActivity();

        return view('admin.dashboard', [
            'registeredClinics' => $totalClinics,
            'services' => $totalServices,
            'users' => $totalUsers,
            'pendingClinics' => $pendingClinics,
            'pendingCount' => $pendingClinicsCount,
            'pendingClinicsCount' => $pendingClinicsCount,
            'totalClinics' => $totalClinics,
            'newClinicsThisMonth' => $newClinicsThisMonth,
            'totalUsers' => $totalUsers,
            'newUsersThisWeek' => $newUsersThisWeek,
            'patientsInQueueNow' => $patientsInQueueNow,
            'appointmentsToday' => $appointmentsToday,
            'cancelledToday' => $cancelledToday,
            'activeClinics' => $activeClinics,
            'rejectedClinics' => $rejectedClinics,
            'topClinicsByQueue' => $topClinicsByQueue,
            'totalServices' => $totalServices,
            'generalServices' => $serviceBreakdown['general'],
            'specialtyServices' => $serviceBreakdown['specialty'],
            'diagnosticServices' => $serviceBreakdown['diagnostic'],
            'registrationTrend' => $registrationTrend,
            'sparklinePoints' => $sparklinePoints,
            'sparklineFillPath' => $sparklineFillPath,
            'recentActivity' => $recentActivity,
        ]);
    }

    private function serviceBreakdown(): array
    {
        $breakdown = [
            'general' => 0,
            'specialty' => 0,
            'diagnostic' => 0,
        ];

        Service::query()
            ->select('name')
            ->get()
            ->each(function (Service $service) use (&$breakdown) {
                $name = strtolower($service->name ?? '');

                if (preg_match('/lab|x-?ray|ultrasound|diagnostic|test|scan|blood|imaging/', $name)) {
                    $breakdown['diagnostic']++;
                    return;
                }

                if (preg_match('/cardio|derma|pedia|ortho|ob|gyne|neuro|dental|eye|ent|psych|surgery|therapy/', $name)) {
                    $breakdown['specialty']++;
                    return;
                }

                $breakdown['general']++;
            });

        return $breakdown;
    }

    private function registrationTrend(): array
    {
        $months = collect(range(5, 0))->map(function (int $monthsAgo) {
            $date = now()->startOfMonth()->subMonths($monthsAgo);

            return [
                'key' => $date->format('Y-m'),
                'label' => $date->format('M'),
                'count' => 0,
            ];
        });

        $counts = Clinic::query()
            ->whereNotNull('created_at')
            ->where('created_at', '>=', now()->startOfMonth()->subMonths(5))
            ->get(['created_at'])
            ->groupBy(fn (Clinic $clinic) => $clinic->created_at->format('Y-m'))
            ->map->count();

        $max = max((int) $counts->max(), 1);

        return $months->map(function (array $month) use ($counts, $max) {
            $count = (int) ($counts[$month['key']] ?? 0);

            return [
                'label' => $month['label'],
                'count' => $count,
                'y' => 62 - (($count / $max) * 52),
            ];
        })->all();
    }

    private function sparklinePaths(array $trend): array
    {
        $lastIndex = max(count($trend) - 1, 1);
        $points = collect($trend)->map(function (array $point, int $index) use ($lastIndex) {
            $x = ($index / $lastIndex) * 420;

            return round($x, 2) . ',' . round($point['y'], 2);
        })->implode(' ');

        $fillPath = 'M0,80 ';
        foreach ($trend as $index => $point) {
            $x = ($index / $lastIndex) * 420;
            $fillPath .= 'L' . round($x, 2) . ',' . round($point['y'], 2) . ' ';
        }
        $fillPath .= 'L420,80 Z';

        return [$points, $fillPath];
    }

    private function recentActivity(): array
    {
        $clinicEvents = Clinic::query()
            ->latest()
            ->take(4)
            ->get(['name', 'status', 'created_at'])
            ->map(function (Clinic $clinic) {
                $status = strtolower((string) $clinic->status);

                return [
                    'description' => match ($status) {
                        'pending' => "{$clinic->name} submitted a clinic application.",
                        'rejected' => "{$clinic->name} application was declined.",
                        default => "{$clinic->name} was registered on the platform.",
                    },
                    'time' => $clinic->created_at?->diffForHumans() ?? '',
                    'timestamp' => $clinic->created_at?->timestamp ?? 0,
                    'icon' => match ($status) {
                        'pending' => 'bi bi-hourglass-split',
                        'rejected' => 'bi bi-x-circle',
                        default => 'bi bi-building-check',
                    },
                    'icon_bg' => match ($status) {
                        'pending' => 'icon-amber',
                        'rejected' => 'icon-rose',
                        default => 'icon-blue',
                    },
                    'icon_color' => match ($status) {
                        'pending' => '#92400e',
                        'rejected' => '#be123c',
                        default => '#1677ff',
                    },
                ];
            });

        $appointmentEvents = Appointment::query()
            ->with('clinic:id,name')
            ->latest()
            ->take(4)
            ->get(['id', 'clinic_id', 'status', 'created_at'])
            ->map(fn (Appointment $appointment) => [
                'description' => 'Appointment ' . str_replace('_', ' ', $appointment->status ?? 'updated') . ' at ' . ($appointment->clinic?->name ?? 'a clinic') . '.',
                'time' => $appointment->created_at?->diffForHumans() ?? '',
                'timestamp' => $appointment->created_at?->timestamp ?? 0,
                'icon' => 'bi bi-calendar-check',
                'icon_bg' => $appointment->status === 'cancelled' ? 'icon-rose' : 'icon-green',
                'icon_color' => $appointment->status === 'cancelled' ? '#be123c' : '#047857',
            ]);

        return $clinicEvents
            ->merge($appointmentEvents)
            ->sortByDesc('timestamp')
            ->take(6)
            ->map(function (array $event) {
                unset($event['timestamp']);

                return $event;
            })
            ->values()
            ->all();
    }
}
