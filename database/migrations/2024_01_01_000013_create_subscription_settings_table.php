<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('monthly_price', 10, 2)->default(5000);
            $table->decimal('yearly_price', 10, 2)->default(50000);
            $table->string('currency', 10)->default('XOF');
            $table->json('mobile_providers')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_settings');
    }
};
