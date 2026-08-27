<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commissionnaire_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['credit', 'withdrawal']);
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('phone')->nullable();
            $table->string('reference')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commission_transactions');
    }
};
