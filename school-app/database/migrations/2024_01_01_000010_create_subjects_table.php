<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');                // e.g., "English Language", "Mathematics"
            $table->string('code')->nullable();    // e.g., "ENG", "MATH"
            $table->enum('level', ['nursery', 'kindergarten', 'primary', 'jhs'])->nullable(); // null = all levels
            $table->boolean('is_elective')->default(false);  // For JHS elective subjects
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
