<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            // 'topo'     = carousel principal
            // 'inferior' = seção inferior
            $table->enum('tipo', ['topo', 'inferior'])
                  ->default('topo')
                  ->after('ativo')
                  ->comment('Posição do banner na página inicial');
        });
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }
};