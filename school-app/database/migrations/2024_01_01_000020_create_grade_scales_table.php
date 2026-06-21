<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('grade_scales', function (Blueprint $table) {
            $table->id();
            $table->enum('level', ['nursery', 'kindergarten', 'primary', 'jhs']);
            $table->decimal('min_score', 5, 2)->default(0);
            $table->decimal('max_score', 5, 2)->default(100);
            $table->string('grade'); // "A", "1", or "Highly Proficient"
            $table->string('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('grade_scales');
    }
};