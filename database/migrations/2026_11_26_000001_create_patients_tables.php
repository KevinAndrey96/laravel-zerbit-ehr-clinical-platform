<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insurers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('nit', 15)->unique();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('document_type', 4);
            $table->string('document_number', 20);
            $table->string('first_name', 60);
            $table->string('second_name', 60)->nullable();
            $table->string('first_surname', 60)->index();
            $table->string('second_surname', 60)->nullable();
            $table->date('birthdate');
            $table->string('sex', 1);
            $table->string('civil_status', 20)->nullable();
            $table->foreignId('insurer_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('occupation', 80)->nullable();
            $table->string('phone', 20);
            $table->string('email')->nullable();
            $table->string('address', 160)->nullable();
            $table->string('city', 80)->nullable();
            $table->timestamps();
            $table->unique(['document_type', 'document_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
        Schema::dropIfExists('insurers');
    }
};
