<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->string('staff_id_number')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('other_names')->nullable();
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female']);
            $table->string('phone');
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('qualification')->nullable();
            $table->date('date_joined');
            $table->string('position');              // e.g., "Class Teacher", "Subject Teacher", "Bursar"
            $table->enum('status', ['active', 'on_leave', 'resigned', 'terminated'])->default('active');
            $table->string('photo')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // Now add the foreign key for class_teacher_id on class_academic_years
        Schema::table('class_academic_years', function (Blueprint $table) {
            $table->foreign('class_teacher_id')->references('id')->on('staff')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('class_academic_years', function (Blueprint $table) {
            $table->dropForeign(['class_teacher_id']);
        });

        Schema::dropIfExists('staff');
    }
};
