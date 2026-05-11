<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SendAppointmentOpeningSmsReminders extends Command
{
    protected $signature = 'appointments:send-opening-sms-reminders';

    protected $description = 'Send SMS reminders to patients 2 hours before clinic operational opening hours.';

    public function handle(): int
    {
        $now = now();
        $today = $now->toDateString();

        $appointments = Appointment::with([
                'clinic',
                'service',
                'doctor',
                'user',
            ])
            ->whereDate('appointment_date', $today)
            ->where('status', 'scheduled')
            ->whereNull('sms_reminder_sent_at')
            ->get();

        if ($appointments->isEmpty()) {
            $this->info('No appointment SMS reminders to send.');
            return self::SUCCESS;
        }

        foreach ($appointments as $appointment) {
            if (! $appointment->clinic) {
                Log::warning('Appointment SMS reminder skipped: missing clinic.', [
                    'appointment_id' => $appointment->id,
                ]);

                continue;
            }

            if (! $appointment->user) {
                Log::warning('Appointment SMS reminder skipped: missing patient/user.', [
                    'appointment_id' => $appointment->id,
                ]);

                continue;
            }

            $openingTime = $this->getClinicOpeningTime((int) $appointment->clinic_id, $now);

            if (! $openingTime) {
                Log::warning('Appointment SMS reminder skipped: no clinic operational opening time found.', [
                    'appointment_id' => $appointment->id,
                    'clinic_id' => $appointment->clinic_id,
                ]);

                continue;
            }

            $openingDateTime = Carbon::parse($today . ' ' . $openingTime);

            /*
             |--------------------------------------------------------------------------
             | Reminder Time
             |--------------------------------------------------------------------------
             | Normal clinic:
             | Opening: 8:00 AM
             | Reminder: 6:00 AM
             |
             | 24-hour clinic:
             | Opening: 12:00 AM
             | 2 hours before is yesterday 10:00 PM.
             | But requirement says "on the day of scheduled appointment",
             | so send it at 12:00 AM instead.
             |--------------------------------------------------------------------------
             */
            $reminderDateTime = $openingDateTime->copy()->subHours(2);
            $startOfAppointmentDay = Carbon::parse($today . ' 00:00:00');

            if ($reminderDateTime->lt($startOfAppointmentDay)) {
                $reminderDateTime = $startOfAppointmentDay;
            }

            /*
             |--------------------------------------------------------------------------
             | Not yet reminder time
             |--------------------------------------------------------------------------
             */
            if ($now->lt($reminderDateTime)) {
                continue;
            }

            $phone = $this->getPatientPhone($appointment);

            if (! $phone) {
                Log::warning('Appointment SMS reminder skipped: patient has no phone number.', [
                    'appointment_id' => $appointment->id,
                    'user_id' => $appointment->user_id,
                ]);

                continue;
            }

            $message = $this->buildMessage($appointment, $openingDateTime);

            $sent = $this->sendSms($phone, $message);

            if (! $sent) {
                continue;
            }

            $appointment->update([
                'sms_reminder_sent_at' => now(),
            ]);

            Log::info('Appointment opening SMS reminder sent.', [
                'appointment_id' => $appointment->id,
                'user_id' => $appointment->user_id,
                'phone_preview' => $this->previewPhone($phone),
            ]);

            $this->info("SMS reminder sent for appointment #{$appointment->id}.");
        }

        return self::SUCCESS;
    }

    private function getClinicOpeningTime(int $clinicId, Carbon $date): ?string
    {
        /*
         |--------------------------------------------------------------------------
         | Flexible operational hours support
         |--------------------------------------------------------------------------
         | This checks common table/column names so it can work with your current DB
         | even if your operational-hours columns are named slightly differently.
         |--------------------------------------------------------------------------
         */

        $possibleTables = [
            'clinic_operational_hours',
            'operational_hours',
            'clinic_hours',
        ];

        $table = null;

        foreach ($possibleTables as $possibleTable) {
            if (Schema::hasTable($possibleTable)) {
                $table = $possibleTable;
                break;
            }
        }

        if (! $table) {
            return null;
        }

        $weekdayName = strtolower($date->format('l')); // monday, tuesday, etc.
        $weekdayNumberIso = (int) $date->dayOfWeekIso; // Monday = 1, Sunday = 7
        $weekdayNumberCarbon = (int) $date->dayOfWeek; // Sunday = 0, Monday = 1

        $query = DB::table($table)
            ->where('clinic_id', $clinicId);

        /*
         |--------------------------------------------------------------------------
         | Day column support
         |--------------------------------------------------------------------------
         | Supports:
         | - day_of_week
         | - weekday
         | - day
         |--------------------------------------------------------------------------
         */
        if (Schema::hasColumn($table, 'day_of_week')) {
            $query->where(function ($q) use ($weekdayName, $weekdayNumberIso, $weekdayNumberCarbon) {
                $q->whereRaw('LOWER(day_of_week) = ?', [$weekdayName])
                    ->orWhere('day_of_week', $weekdayNumberIso)
                    ->orWhere('day_of_week', $weekdayNumberCarbon);
            });
        } elseif (Schema::hasColumn($table, 'weekday')) {
            $query->where(function ($q) use ($weekdayName, $weekdayNumberIso, $weekdayNumberCarbon) {
                $q->whereRaw('LOWER(weekday) = ?', [$weekdayName])
                    ->orWhere('weekday', $weekdayNumberIso)
                    ->orWhere('weekday', $weekdayNumberCarbon);
            });
        } elseif (Schema::hasColumn($table, 'day')) {
            $query->where(function ($q) use ($weekdayName, $weekdayNumberIso, $weekdayNumberCarbon) {
                $q->whereRaw('LOWER(day) = ?', [$weekdayName])
                    ->orWhere('day', $weekdayNumberIso)
                    ->orWhere('day', $weekdayNumberCarbon);
            });
        }

        $hours = $query->first();

        if (! $hours) {
            return null;
        }

        /*
         |--------------------------------------------------------------------------
         | Closed day support
         |--------------------------------------------------------------------------
         */
        foreach (['is_closed', 'closed'] as $column) {
            if (property_exists($hours, $column) && (int) $hours->{$column} === 1) {
                return null;
            }
        }

        /*
         |--------------------------------------------------------------------------
         | 24-hour support
         |--------------------------------------------------------------------------
         */
        foreach (['is_24_hours', 'is_24_hour', 'twenty_four_hours', 'open_24_hours'] as $column) {
            if (property_exists($hours, $column) && (int) $hours->{$column} === 1) {
                return '00:00:00';
            }
        }

        /*
         |--------------------------------------------------------------------------
         | Opening time column support
         |--------------------------------------------------------------------------
         */
        foreach (['open_time', 'opening_time', 'opens_at', 'start_time', 'from_time'] as $column) {
            if (property_exists($hours, $column) && ! empty($hours->{$column})) {
                return Carbon::parse($hours->{$column})->format('H:i:s');
            }
        }

        return null;
    }

    private function getPatientPhone(Appointment $appointment): ?string
    {
        /*
         |--------------------------------------------------------------------------
         | Flexible phone lookup
         |--------------------------------------------------------------------------
         | Your user table might use phone, phone_number, contact_number, or mobile.
         |--------------------------------------------------------------------------
         */

        $user = $appointment->user;

        foreach (['phone', 'phone_number', 'contact_number', 'mobile', 'mobile_number'] as $column) {
            if (isset($user->{$column}) && ! empty($user->{$column})) {
                return $user->{$column};
            }
        }

        return null;
    }

    private function buildMessage(Appointment $appointment, Carbon $openingDateTime): string
    {
        $patientName = $appointment->user->name ?? 'Patient';
        $clinicName = $appointment->clinic->name ?? 'your clinic';
        $serviceName = $appointment->service->name ?? 'your scheduled service';
        $doctorName = $appointment->doctor->name ?? 'your doctor';

        $appointmentDate = Carbon::parse($appointment->appointment_date)->format('F d, Y');

        $appointmentTime = $appointment->appointment_time
            ? Carbon::parse($appointment->appointment_time)->format('h:i A')
            : 'your scheduled time';

        $openingTime = $openingDateTime->format('h:i A');

        return "Hi {$patientName}, this is your CliniQ reminder. "
            . "You have a scheduled appointment today, {$appointmentDate}, at {$appointmentTime} "
            . "for {$serviceName} with {$doctorName} at {$clinicName}. "
            . "The clinic opens at {$openingTime}. Please arrive on time. Thank you.";
    }

    private function sendSms(string $phone, string $message): bool
    {
        try {
            /*
             |--------------------------------------------------------------------------
             | Token-only Mocean setup
             |--------------------------------------------------------------------------
             | Since your token already works, this does NOT use API secret.
             |--------------------------------------------------------------------------
             */
            $token = env('MOCEAN_API_TOKEN');
            $sender = env('MOCEAN_SENDER', 'CliniQ');

            if (! $token) {
                Log::warning('Mocean SMS failed: missing API token.');

                return false;
            }

            $response = Http::asForm()->post('https://rest.moceanapi.com/rest/2/sms', [
                'mocean-api-token' => $token,
                'mocean-from' => $sender,
                'mocean-to' => $this->normalizePhone($phone),
                'mocean-text' => $message,
            ]);

            if (! $response->successful()) {
                Log::warning('Mocean appointment reminder SMS failed.', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'phone_preview' => $this->previewPhone($phone),
                ]);

                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('Mocean appointment reminder SMS exception.', [
                'error' => $e->getMessage(),
                'phone_preview' => $this->previewPhone($phone),
            ]);

            return false;
        }
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D+/', '', $phone);

        if (str_starts_with($phone, '09')) {
            return '63' . substr($phone, 1);
        }

        if (str_starts_with($phone, '9') && strlen($phone) === 10) {
            return '63' . $phone;
        }

        return $phone;
    }

    private function previewPhone(?string $phone): string
    {
        if (! $phone) {
            return 'none';
        }

        $phone = preg_replace('/\D+/', '', $phone);

        if (strlen($phone) <= 6) {
            return 'hidden';
        }

        return substr($phone, 0, 4) . '****' . substr($phone, -2);
    }
}