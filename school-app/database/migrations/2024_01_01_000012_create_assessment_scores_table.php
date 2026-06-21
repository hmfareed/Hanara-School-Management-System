<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('assessment_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_component_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_academic_year_id')->constrained()->cascadeOnDelete();
            $table->decimal('score', 8, 2);
            $table->text('remarks')->nullable();
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestamps();

            $table->unique(['student_id', 'subject_id', 'assessment_component_id', 'class_academic_year_id'], 'unique_assessment_score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_scores');
    }
};