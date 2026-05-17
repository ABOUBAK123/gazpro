<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('livreurs', function (Blueprint $table) {
            // Drop store ownership — livreurs are now platform-wide freelancers
            $table->dropForeign(['store_id']);
            $table->dropColumn('store_id');

            // Real-time GPS position (updated by livreur mobile app)
            $table->decimal('latitude',  10, 7)->nullable()->after('access_token');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');

            // Self-declared availability (livreur toggles on mobile app)
            $table->boolean('is_available')->default(true)->after('longitude');
        });
    }

    public function down(): void
    {
        Schema::table('livreurs', function (Blueprint $table) {
            $table->unsignedBigInteger('store_id')->nullable()->after('id');
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            $table->dropColumn(['latitude', 'longitude', 'is_available']);
        });
    }
};
