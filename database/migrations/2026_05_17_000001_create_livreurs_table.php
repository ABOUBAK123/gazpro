<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('livreurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->string('name');
            $table->string('phone', 20);
            $table->enum('vehicle_type', ['moto', 'tricycle', 'voiture'])->default('moto');
            $table->string('vehicle_plate', 20)->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('access_token', 64)->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('livreurs');
    }
};
