<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE payments MODIFY plan VARCHAR(50) NOT NULL DEFAULT 'mensuel'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE payments MODIFY plan ENUM('monthly','yearly') NOT NULL DEFAULT 'monthly'");
    }
};
