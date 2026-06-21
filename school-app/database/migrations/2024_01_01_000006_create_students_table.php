<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('student_id_number')->unique();  // HAN-{YEAR}-{SEQ}
            $table->string('first_name');
            $table->string('last_name');
            $table->string('other_names')->nullable();
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female']);
            $table->string('photo')->nullable();
            $table->text('address')->nullable();
            $table->string('nationality')->default('Ghanaian');
            $table->string('religion')->nullable();
            $table->string('blood_group')->nullable();
            $table->text('medical_notes')->nullable();
            $table->date('admission_date');
            $table->enum('status', ['active', 'graduated', 'transferred', 'withdrawn'])->default('active');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
