<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('veiculos', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->string('arquivo'); // Nome do arquivo no storage
            $table->string('tipo_arquivo'); // pdf ou excel
            $table->integer('tamanho_kb')->nullable(); // Tamanho em KB
            $table->date('data_publicacao'); // Data de quando foi publicado
            $table->integer('dias_exibicao')->default(15); // Quantos dias ficará visível
            $table->date('data_expiracao'); // Calculado automaticamente
            $table->boolean('ativo')->default(true);
            $table->integer('downloads')->default(0);
            $table->integer('ordem')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('veiculos');
    }
};