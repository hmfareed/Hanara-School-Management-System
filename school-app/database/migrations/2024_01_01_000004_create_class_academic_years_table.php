<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_academic_years', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_class_id')->constrained()->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('class_teacher_id')->nullable();
            $table->timestamps();

            $table->unique(['school_class_id', 'academic_year_id'], 'class_year_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_academic_years');
    }
};
