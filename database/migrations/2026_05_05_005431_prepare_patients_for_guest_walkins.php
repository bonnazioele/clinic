<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            if (! Schema::hasColumn('patients', 'user_id')) {
                $table->foreignId('user_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('patients', 'status')) {
                $table->string('status')->default('guest')->after('patient_number');
            }

            if (! Schema::hasColumn('patients', 'registration_token')) {
                $table->string('registration_token')->nullable()->after('status');
            }

            if (! Schema::hasColumn('patients', 'registration_token_expires_at')) {
                $table->timestamp('registration_token_expires_at')->nullable()->after('registration_token');
            }

            if (! Schema::hasColumn('patients', 'registration_invited_at')) {
                $table->timestamp('registration_invited_at')->nullable()->after('registration_token_expires_at');
            }

            if (! Schema::hasColumn('patients', 'registered_at')) {
                $table->timestamp('registered_at')->nullable()->after('registration_invited_at');
            }
        });

        Schema::table('patients', function (Blueprint $table) {
            if (Schema::hasColumn('patients', 'middle_name')) {
                $table->string('middle_name')->nullable()->change();
            }

            if (Schema::hasColumn('patients', 'sex')) {
                $table->string('sex')->nullable()->change();
            }

            if (Schema::hasColumn('patients', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable()->change();
            }

            if (Schema::hasColumn('patients', 'mobile_number')) {
                $table->string('mobile_number')->nullable()->change();
            }

            if (Schema::hasColumn('patients', 'email_address')) {
                $table->string('email_address')->nullable()->change();
            }

            if (Schema::hasColumn('patients', 'complete_address')) {
                $table->text('complete_address')->nullable()->change();
            }

            if (Schema::hasColumn('patients', 'emergency_contact_name')) {
                $table->string('emergency_contact_name')->nullable()->change();
            }

            if (Schema::hasColumn('patients', 'emergency_contact_relationship')) {
                $table->string('emergency_contact_relationship')->nullable()->change();
            }

            if (Schema::hasColumn('patients', 'emergency_contact_number')) {
                $table->string('emergency_contact_number')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            if (Schema::hasColumn('patients', 'user_id')) {
                $table->dropConstrainedForeignId('user_id');
            }

            if (Schema::hasColumn('patients', 'registered_at')) {
                $table->dropColumn('registered_at');
            }

            if (Schema::hasColumn('patients', 'registration_invited_at')) {
                $table->dropColumn('registration_invited_at');
            }

            if (Schema::hasColumn('patients', 'registration_token_expires_at')) {
                $table->dropColumn('registration_token_expires_at');
            }

            if (Schema::hasColumn('patients', 'registration_token')) {
                $table->dropColumn('registration_token');
            }

            if (Schema::hasColumn('patients', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};