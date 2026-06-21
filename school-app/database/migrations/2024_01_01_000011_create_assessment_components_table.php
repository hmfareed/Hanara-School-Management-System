<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_components', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // "Class Test 1", "Project Work", "End of Term Exam"
            $table->decimal('weight', 5, 2);                 // e.g., 10.00, 20.00, 70.00
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->enum('level', ['nursery', 'kindergarten', 'primary', 'jhs'])->nullable();
            $table->integer('max_score')->default(100);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_components');
    }
};
