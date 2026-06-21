<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_student', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_academic_year_id')->constrained('class_academic_years')->cascadeOnDelete();
            $table->date('enrolled_at');
            $table->enum('status', ['enrolled', 'promoted', 'repeated', 'transferred', 'withdrawn'])->default('enrolled');
            $table->timestamps();

            $table->unique(['student_id', 'class_academic_year_id'], 'student_class_year_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_student');
    }
};
