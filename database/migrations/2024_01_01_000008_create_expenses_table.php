<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->onDelete('cascade');
            $table->enum('type', ['electricity', 'water', 'rent', 'maintenance', 'salary', 'other']);
            $table->string('description')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('XOF');
            $table->date('expense_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
