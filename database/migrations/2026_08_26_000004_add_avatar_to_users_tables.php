<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('name');
        });
        Schema::table('stores', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('owner_name');
        });
        Schema::table('staff', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('avatar');
        });
        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('avatar');
        });
        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn('avatar');
        });
    }
};
