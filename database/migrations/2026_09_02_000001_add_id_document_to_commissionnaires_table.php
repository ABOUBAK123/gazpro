<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commissionnaires', function (Blueprint $table) {
            $table->enum('id_document_type', ['cni', 'passeport'])->nullable()->after('code');
            $table->string('id_document_path')->nullable()->after('id_document_type');
        });
    }

    public function down(): void
    {
        Schema::table('commissionnaires', function (Blueprint $table) {
            $table->dropColumn(['id_document_type', 'id_document_path']);
        });
    }
};
