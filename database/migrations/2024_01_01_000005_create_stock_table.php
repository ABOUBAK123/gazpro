<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->string('brand', 100);
            $table->string('weight', 50);
            $table->integer('quantity')->default(0);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->integer('alert_threshold')->default(5);
            $table->unique(['store_id', 'brand', 'weight']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock');
    }
};
