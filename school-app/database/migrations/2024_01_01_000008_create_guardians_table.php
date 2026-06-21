<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guardians', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone');                 // Primary — SMS goes here
            $table->string('email')->nullable();
            $table->string('relationship');           // "Father", "Mother", "Uncle", etc.
            $table->string('occupation')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_emergency_contact')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guardians');
    }
};
