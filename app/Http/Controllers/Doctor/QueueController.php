<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\QueueEntry;
use App\Models\PatientHistory;
use App\Events\QueueUpdated;

class QueueController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', \App\Http\Middleware\DoctorMiddleware::class]);
    }

    public function index(Request $request)
    {
        $doctor = Auth::user();
        $clinics = $doctor->clinics()->get();

        $activeClinicId = (int) $request->input('clinic_id');

        if (!$activeClinicId || !$clinics->pluck('id')->contains($activeClinicId)) {
            $activeClinicId = (int) optional($clinics->first())->id;
        }

        $waiting = collect();

        if ($activeClinicId) {
            $waiting = QueueEntry::with(['appointment.user', 'appointment.clinic', 'clinic'])
                ->whereIn('status', ['waiting', 'now_serving'])
                ->where('clinic_id', $activeClinicId)
                ->orderByRaw("CASE WHEN status = 'now_serving' THEN 0 ELSE 1 END")
                ->orderBy('created_at')
                ->get();
        }

        return view('doctor.queue.index', compact('waiting', 'clinics', 'activeClinicId'));
    }

    public function serve(Request $request, QueueEntry $entry)
    {
        $doctor = Auth::user();

        if (! $doctor->clinics()->where('clinics.id', $entry->clinic_id)->exists()) {
            abort(403);
        }

        $data = $request->validate([
            'patient_disposition' => 'required|string|in:completed,follow_up,referred,cancelled',
            'diagnosis' => 'nullable|string|max:255',
            'treatment' => 'nullable|string|max:255',
            'doctor_notes' => 'nullable|string|max:1000',
            'prescription' => 'nullable|string|max:1000',
            'follow_up_at' => 'nullable|date',
            'queue_entry_id' => 'nullable|integer',
            'clinic_id' => 'nullable|integer',
        ]);

        \DB::transaction(function () use ($entry, $doctor, $data) {
            $fresh = QueueEntry::lockForUpdate()
                ->with(['appointment.user', 'appointment.clinic', 'clinic'])
                ->find($entry->id);

            if (! $fresh) {
                return;
            }

            if (! in_array($fresh->status, ['waiting', 'now_serving'])) {
                return;
            }

            $fresh->update([
                'status' => 'served',
                'served_at' => now(),
                'patient_disposition' => $data['patient_disposition'],
                'doctor_notes' => $data['doctor_notes'] ?? null,
                'prescription' => $data['prescription'] ?? null,
                'follow_up_at' => $data['follow_up_at'] ?? null,
            ]);

            if ($fresh->appointment && $fresh->appointment->status !== 'completed') {
                $fresh->appointment->update(['status' => 'completed']);
            }

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
                        'doctor'        => $doctor->name,
                        'document_path' => $appointment->medical_document ?? null,
                        'diagnosis'     => null,
                        'treatment'     => null,
                    ]
                );

                $history->doctor = $history->doctor ?: $doctor->name;

                if (!empty($data['diagnosis'])) {
                    $history->diagnosis = $data['diagnosis'];
                }

                if (!empty($data['treatment'])) {
                    $history->treatment = $data['treatment'];
                }

                if (!$history->document_path && $appointment->medical_document) {
                    $history->document_path = $appointment->medical_document;
                }

                $history->save();
            }

            $clinic = $fresh->clinic;
            if ($clinic && $fresh->appointment) {
                $secretaries = $clinic->secretaries()->get();
                foreach ($secretaries as $sec) {
                    $sec->notify(new \App\Notifications\DoctorServedQueue($fresh->appointment));
                }
            }

            event(new QueueUpdated($fresh->fresh(), 'served'));
        });

        return redirect()
            ->route('doctor.queue.index', ['clinic_id' => $entry->clinic_id])
            ->with('status', 'Patient marked done and saved to medical history.');
    }
}