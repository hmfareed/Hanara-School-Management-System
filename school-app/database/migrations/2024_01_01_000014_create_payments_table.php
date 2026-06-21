<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->string('payment_number')->unique();      // For receipts
            $table->decimal('amount', 10, 2);                 // GHS
            $table->enum('method', ['cash', 'momo', 'bank_transfer', 'card']);
            $table->string('reference')->nullable();           // Transaction ID from Paystack/bank
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
