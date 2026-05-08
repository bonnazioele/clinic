<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('queue_entries', function (Blueprint $table) {
            if (! Schema::hasColumn('queue_entries', 'scheduled_slot_date')) {
                $table->date('scheduled_slot_date')->nullable()->after('queue_number');
            }

            if (! Schema::hasColumn('queue_entries', 'scheduled_slot_time')) {
                $table->time('scheduled_slot_time')->nullable()->after('scheduled_slot_date');
            }
        });

        $seenSlots = [];

        DB::table('queue_entries')
            ->join('appointments', 'appointments.id', '=', 'queue_entries.appointment_id')
            ->whereNull('queue_entries.scheduled_slot_date')
            ->whereNull('queue_entries.scheduled_slot_time')
            ->select(
                'queue_entries.id',
                'queue_entries.clinic_id',
                'queue_entries.doctor_id as queue_doctor_id',
                'appointments.doctor_id',
                'appointments.appointment_date',
                'appointments.appointment_time'
            )
            ->orderBy('queue_entries.id')
            ->get()
            ->each(function ($row) use (&$seenSlots) {
                if (! $row->doctor_id || ! $row->appointment_date || ! $row->appointment_time) {
                    return;
                }

                $time = substr((string) $row->appointment_time, 0, 8);
                $time = strlen($time) === 5 ? $time . ':00' : $time;
                $key = $row->clinic_id . ':' . $row->doctor_id . ':' . $row->appointment_date . ':' . $time;

                if (isset($seenSlots[$key])) {
                    return;
                }

                $seenSlots[$key] = true;

                DB::table('queue_entries')
                    ->where('id', $row->id)
                    ->update([
                        'doctor_id' => $row->queue_doctor_id ?: $row->doctor_id,
                        'scheduled_slot_date' => $row->appointment_date,
                        'scheduled_slot_time' => $time,
                    ]);
            });

        Schema::table('queue_entries', function (Blueprint $table) {
            $table->index(
                ['clinic_id', 'doctor_id', 'scheduled_slot_date', 'scheduled_slot_time'],
                'queue_entries_doctor_slot_index'
            );

            $table->unique(
                ['clinic_id', 'doctor_id', 'scheduled_slot_date', 'scheduled_slot_time'],
                'queue_entries_doctor_slot_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('queue_entries', function (Blueprint $table) {
            $table->dropUnique('queue_entries_doctor_slot_unique');
            $table->dropIndex('queue_entries_doctor_slot_index');
        });

        Schema::table('queue_entries', function (Blueprint $table) {
            if (Schema::hasColumn('queue_entries', 'scheduled_slot_time')) {
                $table->dropColumn('scheduled_slot_time');
            }

            if (Schema::hasColumn('queue_entries', 'scheduled_slot_date')) {
                $table->dropColumn('scheduled_slot_date');
            }
        });
    }
};
