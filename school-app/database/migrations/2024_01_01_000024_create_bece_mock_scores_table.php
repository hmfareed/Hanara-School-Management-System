<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bece_mock_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('class_academic_year_id')->constrained()->cascadeOnDelete();
            $table->string('mock_exam_label')->default('Mock 1'); // e.g. "Mock 1", "Mock 2"
            $table->decimal('raw_score', 5, 2);        // raw percentage score
            $table->unsignedTinyInteger('bece_grade');   // 1-9 WAEC grade
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['student_id', 'subject_id', 'class_academic_year_id', 'mock_exam_label'], 'bece_mock_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bece_mock_scores');
    }
};
