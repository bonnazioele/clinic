<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_message_logs', function (Blueprint $table) {
            $table->id();

            $table->nullableMorphs('smsable');
            $table->nullableMorphs('notifiable');

            $table->string('provider')->default('semaphore');
            $table->string('recipient_number', 30)->nullable();
            $table->string('sender_name', 30)->nullable();

            $table->text('message');

            $table->string('status')->default('pending');
            $table->string('provider_message_id')->nullable();

            $table->json('provider_response')->nullable();
            $table->text('error_message')->nullable();

            $table->timestamp('sent_at')->nullable();

            $table->timestamps();

            $table->index(['provider', 'status']);
            $table->index('provider_message_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_message_logs');
    }
};