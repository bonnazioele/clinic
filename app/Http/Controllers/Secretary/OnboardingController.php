<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Models\ClinicOperationalHour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OnboardingController extends Controller
{
    public function operationalHours(Request $request)
    {
        $clinic = $this->resolveActiveClinic($request);
        $clinic->load('operationalHours');

        $hours = collect(ClinicOperationalHour::DAYS)->map(function ($label, $day) use ($clinic) {
            $existing = $clinic->operationalHours->firstWhere('day_of_week', $day);
            $defaultOpen = in_array($day, ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'], true);

            return [
                'day' => $day,
                'label' => $label,
                'is_open' => old("hours.{$day}.is_open", $existing?->is_open ?? $defaultOpen),
                'is_24_hours' => old("hours.{$day}.is_24_hours", $existing?->is_24_hours ?? false),
                'open_time' => old("hours.{$day}.open_time", $this->formatTime($existing?->open_time) ?? '08:00'),
                'close_time' => old("hours.{$day}.close_time", $this->formatTime($existing?->close_time) ?? '17:00'),
                'break_start' => old("hours.{$day}.break_start", $this->formatTime($existing?->break_start)),
                'break_end' => old("hours.{$day}.break_end", $this->formatTime($existing?->break_end)),
            ];
        });

        return view('secretary.onboarding.operational-hours', [
            'clinic' => $clinic,
            'hours' => $hours,
        ]);
    }

    public function storeOperationalHours(Request $request)
    {
        $clinic = $this->resolveActiveClinic($request);
        $days = array_keys(ClinicOperationalHour::DAYS);

        $validated = $request->validate([
            'hours' => ['required', 'array'],
            'hours.*.is_open' => ['nullable', 'boolean'],
            'hours.*.is_24_hours' => ['nullable', 'boolean'],
            'hours.*.open_time' => ['nullable', 'date_format:H:i'],
            'hours.*.close_time' => ['nullable', 'date_format:H:i'],
            'hours.*.break_start' => ['nullable', 'date_format:H:i'],
            'hours.*.break_end' => ['nullable', 'date_format:H:i'],
        ]);

        $submittedHours = $validated['hours'] ?? [];
        $hasOpenDay = false;
        $errors = [];

        foreach ($days as $day) {
            $dayData = $submittedHours[$day] ?? [];
            $isOpen = (bool) ($dayData['is_open'] ?? false);
            $is24Hours = $isOpen && (bool) ($dayData['is_24_hours'] ?? false);

            if (! $isOpen) {
                continue;
            }

            $hasOpenDay = true;

            if ($is24Hours) {
                continue;
            }

            $openTime = $dayData['open_time'] ?? null;
            $closeTime = $dayData['close_time'] ?? null;
            $breakStart = $dayData['break_start'] ?? null;
            $breakEnd = $dayData['break_end'] ?? null;

            if (! $openTime) {
                $errors["hours.{$day}.open_time"] = 'Open time is required when this day is open and not set to 24 hours.';
            }

            if (! $closeTime) {
                $errors["hours.{$day}.close_time"] = 'Close time is required when this day is open and not set to 24 hours.';
            }

            if ($openTime && $closeTime && $closeTime <= $openTime) {
                $errors["hours.{$day}.close_time"] = 'Close time must be later than open time.';
            }

            if (($breakStart && ! $breakEnd) || (! $breakStart && $breakEnd)) {
                $errors["hours.{$day}.break_start"] = 'Please provide both break start and break end, or leave both empty.';
            }

            if ($breakStart && $breakEnd) {
                if ($breakEnd <= $breakStart) {
                    $errors["hours.{$day}.break_end"] = 'Break end must be later than break start.';
                }

                if ($openTime && $closeTime && ($breakStart < $openTime || $breakEnd > $closeTime)) {
                    $errors["hours.{$day}.break_start"] = 'Break time must be within operational hours.';
                }
            }
        }

        if (! $hasOpenDay) {
            $errors['hours'] = 'Please set at least one open day for this clinic.';
        }

        if (! empty($errors)) {
            return back()->withInput()->withErrors($errors);
        }

        DB::transaction(function () use ($clinic, $days, $submittedHours) {
            foreach ($days as $day) {
                $dayData = $submittedHours[$day] ?? [];
                $isOpen = (bool) ($dayData['is_open'] ?? false);
                $is24Hours = $isOpen && (bool) ($dayData['is_24_hours'] ?? false);

                ClinicOperationalHour::updateOrCreate(
                    [
                        'clinic_id' => $clinic->id,
                        'day_of_week' => $day,
                    ],
                    [
                        'sort_order' => ClinicOperationalHour::SORT_ORDER[$day],
                        'is_open' => $isOpen,
                        'is_24_hours' => $is24Hours,
                        'open_time' => ($isOpen && ! $is24Hours) ? ($dayData['open_time'] ?? null) : null,
                        'close_time' => ($isOpen && ! $is24Hours) ? ($dayData['close_time'] ?? null) : null,
                        'break_start' => ($isOpen && ! $is24Hours) ? ($dayData['break_start'] ?? null) : null,
                        'break_end' => ($isOpen && ! $is24Hours) ? ($dayData['break_end'] ?? null) : null,
                    ]
                );
            }

            $clinic->forceFill([
                'operational_hours_configured' => true,
                'setup_completed_at' => now(),
            ])->save();
        });

        return redirect()
            ->route('secretary.onboarding.ready')
            ->with('status', 'Operational hours saved successfully.');
    }

    public function ready(Request $request)
    {
        $clinic = $this->resolveActiveClinic($request);
        $clinic->load('operationalHours');

        return view('secretary.onboarding.ready', [
            'clinic' => $clinic,
        ]);
    }

    public function finish(Request $request)
    {
        $clinic = $this->resolveActiveClinic($request);

        if (! $clinic->hasConfiguredOperationalHours()) {
            return redirect()
                ->route('secretary.onboarding.operational-hours')
                ->with('warning', 'Please configure operational hours first.');
        }

        if (! $clinic->setup_completed_at) {
            $clinic->forceFill(['setup_completed_at' => now()])->save();
        }

        return redirect()->route('secretary.dashboard')
            ->with('status', 'Your clinic is ready now.');
    }

    private function resolveActiveClinic(Request $request): Clinic
    {
        $user = $request->user();
        $clinicId = session('active_clinic_id')
            ?? session('selected_clinic_id')
            ?? $request->query('clinic_id');

        $query = Clinic::query()
            ->whereHas('secretaries', function ($secretaryQuery) use ($user) {
                $secretaryQuery->where('users.id', $user->id);
            });

        if ($clinicId) {
            $clinic = (clone $query)->where('clinics.id', $clinicId)->first();

            if ($clinic) {
                session(['active_clinic_id' => $clinic->id]);
                return $clinic;
            }
        }

        $clinic = $query->orderBy('clinics.name')->firstOrFail();
        session(['active_clinic_id' => $clinic->id]);

        return $clinic;
    }

    private function formatTime($value): ?string
    {
        if (! $value) {
            return null;
        }

        if ($value instanceof \Carbon\CarbonInterface) {
            return $value->format('H:i');
        }

        return substr((string) $value, 0, 5);
    }
}
