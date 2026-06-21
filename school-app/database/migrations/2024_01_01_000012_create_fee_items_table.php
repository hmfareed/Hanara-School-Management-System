<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');                           // "Tuition", "Feeding", "Transport", "PTA Levy"
            $table->decimal('amount', 10, 2);                 // In GHS
            $table->foreignId('academic_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('term_id')->nullable()->constrained()->nullOnDelete();          // null = all terms
            $table->foreignId('school_class_id')->nullable()->constrained()->nullOnDelete();   // null = all classes
            $table->boolean('is_optional')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_items');
    }
};
