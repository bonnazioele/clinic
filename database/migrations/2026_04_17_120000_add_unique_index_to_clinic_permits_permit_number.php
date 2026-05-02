<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Normalize historical duplicates so the new unique index can be applied safely.
        $duplicateNumbers = DB::table('clinic_permits')
            ->select('permit_number')
            ->whereNotNull('permit_number')
            ->where('permit_number', '!=', '')
            ->groupBy('permit_number')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('permit_number');

        foreach ($duplicateNumbers as $number) {
            $ids = DB::table('clinic_permits')
                ->where('permit_number', $number)
                ->orderBy('id')
                ->pluck('id')
                ->values();

            // Keep the first record unchanged and make subsequent values unique.
            foreach ($ids->slice(1) as $permitId) {
                $base = substr((string) $number, 0, 240);
                $candidate = $base.'-'.$permitId;
                $suffix = 1;

                while (DB::table('clinic_permits')->where('permit_number', $candidate)->exists()) {
                    $candidate = $base.'-'.$permitId.'-'.$suffix;
                    $suffix++;
                }

                DB::table('clinic_permits')
                    ->where('id', $permitId)
                    ->update(['permit_number' => $candidate]);
            }
        }

        Schema::table('clinic_permits', function (Blueprint $table) {
            $table->unique('permit_number', 'clinic_permits_permit_number_unique');
        });
    }

    public function down(): void
    {
        Schema::table('clinic_permits', function (Blueprint $table) {
            $table->dropUnique('clinic_permits_permit_number_unique');
        });
    }
};
