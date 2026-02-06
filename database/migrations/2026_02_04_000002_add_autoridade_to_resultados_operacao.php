<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('resultados_operacao', function (Blueprint $table) {
            // Adicionar campos de autoridade responsável
            $table->string('autoridade_responsavel_nome')->after('unidade_policial_responsavel');
            $table->string('autoridade_responsavel_matricula', 50)->after('autoridade_responsavel_nome');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resultados_operacao', function (Blueprint $table) {
            $table->dropColumn(['autoridade_responsavel_nome', 'autoridade_responsavel_matricula']);
        });
    }
};
