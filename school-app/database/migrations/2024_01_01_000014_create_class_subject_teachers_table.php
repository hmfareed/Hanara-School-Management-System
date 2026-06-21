<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('class_subject_teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['class_academic_year_id', 'subject_id'], 'unique_class_subject');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_subject_teachers');
    }
};