<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('livreur_id')->nullable()->after('client_id')
                  ->constrained('livreurs')->onDelete('set null');
        });

        // Add en_route to status enum
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending','confirmed','en_route','delivered','cancelled') DEFAULT 'pending'");
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['livreur_id']);
            $table->dropColumn('livreur_id');
        });
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending','confirmed','delivered','cancelled') DEFAULT 'pending'");
    }
};
