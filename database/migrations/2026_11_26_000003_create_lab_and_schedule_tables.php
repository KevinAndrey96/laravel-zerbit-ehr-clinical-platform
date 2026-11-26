<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_samples', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->foreignId('patient_id')->constrained()->restrictOnDelete();
            $table->foreignId('registered_by')->constrained('users')->restrictOnDelete();
            $table->string('sample_type', 20);
            $table->string('exam', 120);
            $table->string('authorization_number', 30)->nullable();
            $table->date('taken_on');
            $table->string('status', 20)->index();
            $table->boolean('notify_by_email')->default(false);
            $table->string('result_path')->nullable();
            $table->timestamp('result_ready_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->string('delivery_channel', 20)->nullable();
            $table->timestamps();
        });

        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->restrictOnDelete();
            $table->foreignId('therapist_id')->constrained('users')->restrictOnDelete();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('status', 20);
            $table->text('notes')->nullable();
            $table->string('cancellation_reason', 160)->nullable();
            $table->timestamp('reminder_sent_at')->nullable();
            $table->timestamps();
            $table->index(['therapist_id', 'starts_at']);
            $table->index(['patient_id', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('lab_samples');
    }
};
