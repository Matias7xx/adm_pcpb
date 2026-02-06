<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Passo 1: Criar colunas temporárias JSON
        Schema::table('resultados_operacao', function (Blueprint $table) {
            $table->json('tipo_arma_apreendida_temp')->nullable()->after('quantidade_armas_apreendidas');
            $table->json('entorpecente_apreendido_temp')->nullable()->after('municoes_apreendidas');
        });

        // Passo 2: Migrar dados antigos para as novas colunas (convertendo string para array JSON)
        DB::statement("
            UPDATE resultados_operacao 
            SET tipo_arma_apreendida_temp = 
                CASE 
                    WHEN tipo_arma_apreendida IS NOT NULL 
                    THEN json_build_array(tipo_arma_apreendida::text)
                    ELSE NULL
                END
        ");

        DB::statement("
            UPDATE resultados_operacao 
            SET entorpecente_apreendido_temp = 
                CASE 
                    WHEN entorpecente_apreendido IS NOT NULL 
                    THEN json_build_array(entorpecente_apreendido::text)
                    ELSE NULL
                END
        ");

        // Passo 3: Dropar colunas antigas (ENUM)
        Schema::table('resultados_operacao', function (Blueprint $table) {
            $table->dropColumn(['tipo_arma_apreendida', 'entorpecente_apreendido']);
        });

        // Passo 4: Renomear colunas temporárias para os nomes originais
        Schema::table('resultados_operacao', function (Blueprint $table) {
            $table->renameColumn('tipo_arma_apreendida_temp', 'tipo_arma_apreendida');
            $table->renameColumn('entorpecente_apreendido_temp', 'entorpecente_apreendido');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Passo 1: Criar colunas temporárias como TEXT
        Schema::table('resultados_operacao', function (Blueprint $table) {
            $table->text('tipo_arma_apreendida_temp')->nullable()->after('quantidade_armas_apreendidas');
            $table->text('entorpecente_apreendido_temp')->nullable()->after('municoes_apreendidas');
        });

        // Passo 2: Migrar dados de volta (pegando o primeiro item do array)
        DB::statement("
            UPDATE resultados_operacao 
            SET tipo_arma_apreendida_temp = tipo_arma_apreendida::jsonb->>0
            WHERE tipo_arma_apreendida IS NOT NULL
        ");

        DB::statement("
            UPDATE resultados_operacao 
            SET entorpecente_apreendido_temp = entorpecente_apreendido::jsonb->>0
            WHERE entorpecente_apreendido IS NOT NULL
        ");

        // Passo 3: Dropar colunas JSON
        Schema::table('resultados_operacao', function (Blueprint $table) {
            $table->dropColumn(['tipo_arma_apreendida', 'entorpecente_apreendido']);
        });

        // Passo 4: Renomear e recriar como ENUM
        Schema::table('resultados_operacao', function (Blueprint $table) {
            $table->renameColumn('tipo_arma_apreendida_temp', 'tipo_arma_apreendida');
            $table->renameColumn('entorpecente_apreendido_temp', 'entorpecente_apreendido');
        });

        // Passo 5: Alterar para ENUM (pode falhar se houver valores incompatíveis)
        DB::statement("
            ALTER TABLE resultados_operacao 
            ALTER COLUMN tipo_arma_apreendida TYPE VARCHAR(50)
        ");

        DB::statement("
            ALTER TABLE resultados_operacao 
            ALTER COLUMN entorpecente_apreendido TYPE VARCHAR(50)
        ");
    }
};