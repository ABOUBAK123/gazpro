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
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('XOF');
            $table->enum('payment_method', ['card', 'orange_money', 'moov_money', 'other'])->default('card');
            $table->string('reference')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->enum('plan', ['monthly', 'yearly'])->default('monthly');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
