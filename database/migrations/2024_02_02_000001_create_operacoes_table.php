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
        Schema::create('operacoes', function (Blueprint $table) {
            $table->id();
            
            // Dados do responsável pelo preenchimento (automático do usuário logado)
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('policial_responsavel_nome');
            $table->string('policial_responsavel_matricula');
            $table->string('unidade_policial_responsavel');
            
            // Dados da operação
            $table->string('nome_operacao');
            $table->string('autoridade_responsavel_nome');
            $table->string('autoridade_responsavel_matricula');
            
            // Origem e localização
            $table->enum('origem_operacao', ['Nacional', 'Estadual', 'Apoio a outro Estado']);
            $table->string('uf_responsavel', 2); // UF
            $table->date('data_operacao');
            
            // Briefing
            $table->string('local_briefing');
            $table->time('horario_briefing');
            
            // Quantidades (números)
            $table->integer('quantidade_total_alvos')->unsigned();
            $table->integer('quantidade_mandados_prisao')->unsigned();
            $table->integer('quantidade_mandados_busca_apreensao')->unsigned();
            $table->integer('quantidade_mandados_busca_apreensao_infrator')->unsigned();
            $table->integer('quantidade_alvos_outros_estados')->unsigned();
            $table->integer('quantidade_policiais_empregados')->unsigned();
            $table->integer('quantidade_viaturas_empregadas')->unsigned();
            
            // Informações da operação
            $table->text('cidades_alvo');
            $table->text('crimes_investigados');
            
            // Vinculações
            $table->enum('vinculada_unidade', [
                'Delegacia Geral',
                '1ª SRPC',
                '2ª SRPC',
                '3ª SRPC',
                '4ª SRPC',
                'COORDEAM',
                'DIOP'
            ]);
            
            $table->enum('vinculada_unidade_especializada', [
                'DAV',
                'DCCPAT',
                'DCCPES',
                'DDF',
                'DEAM',
                'DEATI',
                'DEATUR',
                'DECC',
                'DECCOR',
                'DECCOT',
                'DECHRADI',
                'DECON',
                'DESARME',
                'DHE',
                'DMA',
                'DRACO',
                'DRE',
                'DRFVC',
                'GOE',
                'GTE',
                'DIJ',
                'DRCCIJ',
                'DRF',
                'NH',
                'NRQ',
                'OUTRA'
            ]);
            
            // Campo opcional para outra unidade
            $table->string('outra_unidade_policial')->nullable();
            
            $table->enum('vinculada_delegacia_seccional', [
                '1ª DSPC', '2ª DSPC', '3ª DSPC', '4ª DSPC', '5ª DSPC',
                '6ª DSPC', '7ª DSPC', '8ª DSPC', '9ª DSPC', '10ª DSPC',
                '11ª DSPC', '12ª DSPC', '13ª DSPC', '14ª DSPC', '15ª DSPC',
                '16ª DSPC', '17ª DSPC', '18ª DSPC', '19ª DSPC', '20ª DSPC',
                '21ª DSPC', '22ª DSPC', '23ª DSPC', '24ª DSPC', 'PREJUDICADO'
            ]);
            
            // Solicitação de apoio
            $table->text('solicitacao_apoio_diop')->nullable();
            
            $table->timestamps();
            
            // Índices para otimização de queries no Metabase
            $table->index('unidade_policial_responsavel');
            $table->index('data_operacao');
            $table->index('origem_operacao');
            $table->index('uf_responsavel');
            $table->index('vinculada_unidade');
            $table->index('vinculada_unidade_especializada');
            $table->index('vinculada_delegacia_seccional');
            $table->index(['unidade_policial_responsavel', 'data_operacao']); // Índice composto
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('operacoes');
    }
};
