<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            // Expand name length
            $table->string('name', 255)->change();

            // Add type_id foreign key
            $table->foreignId('type_id')->nullable()->after('name')->constrained('clinic_types')->nullOnDelete();

            // Add new columns
            $table->string('branch_code', 50)->unique()->after('type_id');
            $table->string('contact_number', 50)->after('address');
            $table->string('email', 100)->unique()->after('contact_number');
            $table->string('logo', 255)->nullable()->after('email');

            $table->enum('status', ['Approved', 'Rejected', 'Pending'])->default('Pending')->after('logo');

            // Rename latitude and longitude to gps_ equivalents
            $table->renameColumn('latitude', 'gps_latitude');
            $table->renameColumn('longitude', 'gps_longitude');

            // Add new approval and rejection metadata
            $table->timestamp('approved_at')->nullable()->after('gps_longitude');
            $table->foreignId('approved_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();

            $table->timestamp('rejected_at')->nullable()->after('approved_by');
            $table->foreignId('rejected_by')->nullable()->after('rejected_at')->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable()->after('rejected_by');

            // Add soft deletes
            $table->softDeletes();

            // Add indexes
            $table->index('status', 'idx_status');
            $table->index('type_id', 'idx_type_id');
            $table->index(['gps_latitude', 'gps_longitude'], 'idx_location');
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            // Rollback new columns
            $table->dropForeign(['type_id']);
            $table->dropColumn(['type_id', 'branch_code', 'contact_number', 'email', 'logo', 'status']);

            $table->renameColumn('gps_latitude', 'latitude');
            $table->renameColumn('gps_longitude', 'longitude');

            $table->dropForeign(['approved_by']);
            $table->dropForeign(['rejected_by']);
            $table->dropColumn(['approved_at', 'approved_by', 'rejected_at', 'rejected_by', 'rejection_reason']);

            $table->dropSoftDeletes();

            // Drop indexes
            $table->dropIndex('idx_status');
            $table->dropIndex('idx_type_id');
            $table->dropIndex('idx_location');

            // Revert name length if needed (optional)
            $table->string('name')->change();
        });
    }
};
