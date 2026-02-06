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
        Schema::create('resultados_operacao', function (Blueprint $table) {
            $table->id();
            
            // Relacionamento com a operação
            $table->foreignId('operacao_id')->constrained('operacoes')->onDelete('cascade');
            
            // Dados do responsável pelo preenchimento (automático do usuário logado)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('policial_responsavel_nome');
            $table->string('policial_responsavel_matricula');
            $table->string('unidade_policial_responsavel');
            
            // Número do processo PJE (opcional)
            $table->string('numero_processo_pje')->nullable();
            
            // Mandados de prisão
            $table->integer('mandados_prisao_cumpridos')->unsigned();
            $table->text('mandados_prisao_cumpridos_detalhes')->nullable(); // Nome e CPF dos presos
            $table->integer('mandados_prisao_nao_cumpridos')->unsigned();
            
            // Mandados de busca e apreensão
            $table->integer('mandados_busca_cumpridos')->unsigned();
            $table->integer('mandados_busca_infrator_cumpridos')->unsigned();
            $table->integer('mandados_busca_infrator_nao_cumpridos')->unsigned();
            
            // Prisões em flagrante
            $table->integer('prisoes_flagrante')->unsigned();
            
            // Armas apreendidas
            $table->integer('quantidade_armas_apreendidas')->unsigned();
            $table->enum('tipo_arma_apreendida', [
                'REVÓLVER',
                'PISTOLA',
                'ESPINGARDA',
                'FUZIL',
                'ARMA ARTESANAL',
                'EXPLOSIVO',
                'PREJUDICADO'
            ])->nullable();
            $table->text('detalhes_armas_apreendidas')->nullable(); // Quantificar tipo/calibre quando mais de uma
            
            // Munições
            $table->text('municoes_apreendidas')->nullable(); // Quantidade e calibre
            
            // Entorpecentes
            $table->enum('entorpecente_apreendido', [
                'NENHUM',
                'MACONHA',
                'COCAINA',
                'CRACK',
                'SKANK',
                'HEROINA',
                'LSD',
                'ECSTASY (MDMA)',
                'OUTROS'
            ]);
            $table->text('detalhes_entorpecentes')->nullable(); // Peso/quantidade quando mais de um tipo
            
            // Valores e objetos apreendidos
            $table->decimal('valores_dinheiro', 15, 2)->default(0);
            $table->text('veiculos_apreendidos')->nullable(); // Marca/modelo/placa/ano/cor/valor
            $table->text('demais_objetos_apreendidos')->nullable(); // Com valor estimado
            
            // Outras informações (opcional)
            $table->text('outras_informacoes')->nullable();
            
            $table->timestamps();
            
            // Índices para otimização de queries no Metabase
            $table->index('operacao_id');
            $table->index('unidade_policial_responsavel');
            $table->index('created_at');
            $table->index('entorpecente_apreendido');
            $table->index('tipo_arma_apreendida');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resultados_operacao');
    }
};
