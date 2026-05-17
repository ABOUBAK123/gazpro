<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loyalty_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade')->unique();
            $table->integer('points_per_unit')->default(1);
            $table->integer('reward_threshold')->default(100);
            $table->decimal('reward_value', 10, 2)->default(1000);
            $table->string('currency', 10)->default('XOF');
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loyalty_programs');
    }
};
