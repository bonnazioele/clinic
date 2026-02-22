<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\QueueEntry;
use App\Models\Appointment;
use App\Models\PatientHistory;
use App\Events\QueueUpdated;
use Illuminate\Validation\Rule;

class QueueController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\DoctorMiddleware::class]);
    }

    public function index()
    {
        $doctor = Auth::user();

        $waiting = QueueEntry::with(['appointment.user', 'appointment.clinic', 'clinic'])
            ->whereIn('status', ['waiting', 'now_serving'])
            ->whereIn('clinic_id', $doctor->clinics()->pluck('clinics.id'))
            ->orderBy('created_at')
            ->get();

        return view('doctor.queue.index', compact('waiting'));
    }

    public function serve(Request $request, QueueEntry $entry)
    {
        $doctor = Auth::user();

        if (! $doctor->clinics()->where('clinics.id', $entry->clinic_id)->exists()) {
            abort(403);
        }

        $data = $request->validate([
            'diagnosis' => 'nullable|string|max:255',
            'treatment' => 'nullable|string|max:255',
        ]);

        \DB::transaction(function () use ($entry, $doctor, $data) {
            $fresh = QueueEntry::lockForUpdate()
                ->with(['appointment.user', 'appointment.clinic', 'clinic'])
                ->find($entry->id);

            if (! $fresh) return;

            if (! in_array($fresh->status, ['waiting','now_serving'])) {
                return;
            }

            // Mark served
            $fresh->update([
                'status' => 'served',
                'served_at' => now(),
                'patient_disposition' => $data['patient_disposition'],
                'doctor_notes' => $data['doctor_notes'] ?? null,
                'prescription' => $data['prescription'] ?? null,
                'follow_up_at' => $data['follow_up_at'] ?? null,
            ]);

            // Mark appointment completed
            if ($fresh->appointment && $fresh->appointment->status !== 'completed') {
                $fresh->appointment->update(['status' => 'completed']);
            }

            // ✅ Create / Update patient history with doctor name + diagnosis/treatment
            if ($fresh->appointment) {
                $appointment = $fresh->appointment;

                $clinicName = optional($fresh->clinic)->name
                    ?? optional($appointment->clinic)->name
                    ?? 'Unknown Clinic';

                $history = PatientHistory::firstOrCreate(
                    [
                        'user_id'       => $appointment->user_id,
                        'clinic_name'   => $clinicName,
                        'date_of_visit' => $appointment->appointment_date,
                    ],
                    [
                        'doctor'        => $doctor->name, // ✅ ACTUAL logged-in doctor
                        'document_path' => $appointment->medical_document ?? null,
                        'diagnosis'     => null,
                        'treatment'     => null,
                    ]
                );

                // Always ensure doctor is saved even if record existed
                $history->doctor = $history->doctor ?: $doctor->name;

                // Save diagnosis/treatment if provided
                if (!empty($data['diagnosis'])) $history->diagnosis = $data['diagnosis'];
                if (!empty($data['treatment'])) $history->treatment = $data['treatment'];

                // Attach document if history doesn't have it yet
                if (!$history->document_path && $appointment->medical_document) {
                    $history->document_path = $appointment->medical_document;
                }

                $history->save();
            }

            // Notify secretaries (your existing logic)
            $clinic = $fresh->clinic;
            if ($clinic && $fresh->appointment) {
                $secretaries = $clinic->secretaries()->get();
                foreach ($secretaries as $sec) {
                    $sec->notify(new \App\Notifications\DoctorServedQueue($fresh->appointment));
                }
            }

            event(new QueueUpdated($fresh->fresh(), 'served'));
        });

        return back()->with('status', 'Patient marked done and saved to medical history.');
    }
}