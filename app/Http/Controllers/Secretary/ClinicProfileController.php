<?php

namespace App\Http\Controllers\Secretary;

use App\Http\Controllers\Concerns\InteractsWithClinic;
use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsureSelectedClinic;
use App\Models\Clinic;
use App\Models\ClinicOperationalHour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ClinicProfileController extends Controller
{
    use InteractsWithClinic;

    public function __construct()
    {
        $this->middleware([
            'auth',
            \App\Http\Middleware\SecretaryMiddleware::class,
            EnsureSelectedClinic::class,
        ]);
    }

    public function show(Request $request, Clinic $clinic)
    {
        $this->authorizeClinic($request, $clinic);

        $clinic->load(['operationalHours']);
        $hours = $this->hoursByDay($clinic);

        return view('secretary.clinic.show', compact('clinic', 'hours'));
    }

    public function edit(Request $request, Clinic $clinic)
    {
        $this->authorizeClinic($request, $clinic);

        $clinic->load(['operationalHours']);
        $hours = $this->hoursByDay($clinic);

        return view('secretary.clinic.edit', compact('clinic', 'hours'));
    }

    public function update(Request $request, Clinic $clinic)
    {
        $this->authorizeClinic($request, $clinic);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:3072'],
            'cover_image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            'hours' => ['required', 'array'],
        ]);

        $hoursInput = $request->input('hours', []);
        $this->validateOperationalHours($hoursInput);

        DB::transaction(function () use ($request, $clinic, $data, $hoursInput) {
            $clinicData = [
                'name' => $data['name'],
                'address' => $data['address'],
                'description' => $data['description'] ?? null,
                'operational_hours_configured' => true,
            ];

            if (! $clinic->setup_completed_at) {
                $clinicData['setup_completed_at'] = now();
            }

            if ($request->hasFile('logo')) {
                if ($clinic->logo && Storage::disk('public')->exists($clinic->logo)) {
                    Storage::disk('public')->delete($clinic->logo);
                }

                $clinicData['logo'] = $request->file('logo')->store('clinic-logos', 'public');
            }

            if ($request->hasFile('cover_image')) {
                if ($clinic->cover_image && Storage::disk('public')->exists($clinic->cover_image)) {
                    Storage::disk('public')->delete($clinic->cover_image);
                }

                $clinicData['cover_image'] = $request->file('cover_image')->store('clinic-covers', 'public');
            }

            $clinic->update($clinicData);

            foreach (ClinicOperationalHour::DAYS as $dayKey => $dayLabel) {
                $row = $hoursInput[$dayKey] ?? [];

                $isOpen = $request->boolean("hours.{$dayKey}.is_open");
                $is24Hours = $isOpen && $request->boolean("hours.{$dayKey}.is_24_hours");

                ClinicOperationalHour::updateOrCreate(
                    [
                        'clinic_id' => $clinic->id,
                        'day_of_week' => $dayKey,
                    ],
                    [
                        'sort_order' => ClinicOperationalHour::SORT_ORDER[$dayKey],
                        'is_open' => $isOpen,
                        'is_24_hours' => $is24Hours,
                        'open_time' => $isOpen && ! $is24Hours ? ($row['open_time'] ?? null) : null,
                        'close_time' => $isOpen && ! $is24Hours ? ($row['close_time'] ?? null) : null,
                        'break_start' => $isOpen && ! $is24Hours ? ($row['break_start'] ?? null) : null,
                        'break_end' => $isOpen && ! $is24Hours ? ($row['break_end'] ?? null) : null,
                    ]
                );
            }
        });

        return redirect()
            ->route('secretary.clinic.show', $clinic)
            ->with('status', 'Clinic settings and operational hours updated.');
    }

    private function authorizeClinic(Request $request, Clinic $clinic): void
    {
        if ((int) $clinic->id !== (int) $this->activeClinicId($request)) {
            abort(403);
        }
    }

    private function hoursByDay(Clinic $clinic)
    {
        return $clinic->operationalHours
            ->keyBy('day_of_week')
            ->union(collect(ClinicOperationalHour::DAYS)->mapWithKeys(function ($label, $dayKey) use ($clinic) {
                return [
                    $dayKey => new ClinicOperationalHour([
                        'clinic_id' => $clinic->id,
                        'day_of_week' => $dayKey,
                        'sort_order' => ClinicOperationalHour::SORT_ORDER[$dayKey],
                        'is_open' => false,
                        'is_24_hours' => false,
                    ]),
                ];
            }))
            ->sortBy(fn ($hour) => ClinicOperationalHour::SORT_ORDER[$hour->day_of_week] ?? 99);
    }

    private function validateOperationalHours(array $hoursInput): void
    {
        $errors = [];
        $hasOpenDay = false;

        foreach (ClinicOperationalHour::DAYS as $dayKey => $dayLabel) {
            $row = $hoursInput[$dayKey] ?? [];

            $isOpen = array_key_exists('is_open', $row);
            $is24Hours = $isOpen && array_key_exists('is_24_hours', $row);

            if (! $isOpen) {
                continue;
            }

            $hasOpenDay = true;

            if ($is24Hours) {
                continue;
            }

            $openTime = $row['open_time'] ?? null;
            $closeTime = $row['close_time'] ?? null;
            $breakStart = $row['break_start'] ?? null;
            $breakEnd = $row['break_end'] ?? null;

            if (! $openTime) {
                $errors["hours.{$dayKey}.open_time"] = "Open time is required for {$dayLabel}.";
            }

            if (! $closeTime) {
                $errors["hours.{$dayKey}.close_time"] = "Close time is required for {$dayLabel}.";
            }

            if ($openTime && $closeTime && $openTime >= $closeTime) {
                $errors["hours.{$dayKey}.close_time"] = "Close time must be after open time for {$dayLabel}.";
            }

            if (($breakStart && ! $breakEnd) || (! $breakStart && $breakEnd)) {
                $errors["hours.{$dayKey}.break_start"] = "Both break start and break end are required for {$dayLabel}.";
            }

            if ($breakStart && $breakEnd) {
                if ($breakStart >= $breakEnd) {
                    $errors["hours.{$dayKey}.break_end"] = "Break end must be after break start for {$dayLabel}.";
                }

                if ($openTime && $closeTime && ($breakStart < $openTime || $breakEnd > $closeTime)) {
                    $errors["hours.{$dayKey}.break_start"] = "Break time must be inside operating hours for {$dayLabel}.";
                }
            }
        }

        if (! $hasOpenDay) {
            $errors['hours'] = 'Please set at least one open day for the clinic.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }
}
