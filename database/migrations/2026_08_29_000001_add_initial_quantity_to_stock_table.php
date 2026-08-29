<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock', function (Blueprint $table) {
            $table->integer('initial_quantity')->nullable()->after('quantity');
            $table->timestamp('restocked_at')->nullable()->after('alert_threshold');
        });

        // Backfill existing rows so the new "Stock initial" column isn't
        // empty for items already in inventory before this migration.
        DB::table('stock')->update([
            'initial_quantity' => DB::raw('quantity'),
            'restocked_at'     => DB::raw('updated_at'),
        ]);
    }

    public function down(): void
    {
        Schema::table('stock', function (Blueprint $table) {
            $table->dropColumn(['initial_quantity', 'restocked_at']);
        });
    }
};
