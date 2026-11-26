<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public const array BACKGROUND = ['pathological', 'surgical', 'traumatic', 'allergic', 'family', 'pharmacological', 'other'];

    public const array ASSESSMENT = ['edema', 'joint_mobility', 'sensitivity', 'skin', 'muscle_strength', 'flexibility', 'posture', 'gait', 'balance', 'other_findings'];

    public function up(): void
    {
        Schema::create('clinical_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->restrictOnDelete();
            $table->foreignId('therapist_id')->constrained('users')->restrictOnDelete();
            $table->date('opened_on');
            $table->text('reason');
            $table->string('diagnosis_code', 8);
            $table->text('diagnosis');
            $table->text('physio_diagnosis');
            $table->text('objective');
            $table->unsignedSmallInteger('authorized_sessions');
            $table->string('authorization_number', 30)->nullable();
            $table->unsignedInteger('copayment_cop')->nullable();
            foreach (self::BACKGROUND as $field) {
                $table->text('background_'.$field)->nullable();
            }
            $table->unsignedTinyInteger('pain_scale')->nullable();
            $table->text('pain_notes')->nullable();
            foreach (self::ASSESSMENT as $field) {
                $table->text($field)->nullable();
            }
            $table->string('fall_risk', 10)->nullable();
            $table->date('closed_on')->nullable()->index();
            $table->text('discharge_summary')->nullable();
            $table->timestamps();
        });

        Schema::create('evolutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('clinical_history_id')->constrained()->restrictOnDelete();
            $table->foreignId('therapist_id')->constrained('users')->restrictOnDelete();
            $table->unsignedSmallInteger('session_number');
            $table->date('attended_on');
            $table->time('starts_at');
            $table->time('ends_at');
            $table->unsignedTinyInteger('pain_scale')->nullable();
            $table->text('note');
            $table->string('patient_signature_path')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();
            $table->unique(['clinical_history_id', 'session_number']);
        });

        Schema::create('access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('patient_id')->constrained()->restrictOnDelete();
            $table->foreignId('clinical_history_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('action', 20);
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('access_logs');
        Schema::dropIfExists('evolutions');
        Schema::dropIfExists('clinical_histories');
    }
};
