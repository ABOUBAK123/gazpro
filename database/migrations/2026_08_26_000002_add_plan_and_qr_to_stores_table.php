<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->foreignId('plan_id')->nullable()->after('subscription_expiry')->constrained()->nullOnDelete();
            $table->uuid('qr_token')->nullable()->unique()->after('plan_id');
            $table->string('qr_code_path')->nullable()->after('qr_token');
            $table->timestamp('qr_generated_at')->nullable()->after('qr_code_path');
        });
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropConstrainedForeignId('plan_id');
            $table->dropColumn(['qr_token', 'qr_code_path', 'qr_generated_at']);
        });
    }
};
