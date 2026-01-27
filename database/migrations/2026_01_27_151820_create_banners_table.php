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
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->string('descricao')->nullable();
            $table->string('imagem'); // Caminho da imagem no storage
            $table->string('link')->nullable(); // URL para redirecionar ao clicar (opcional)
            $table->boolean('nova_aba')->default(true); // Abrir link em nova aba
            $table->integer('ordem')->default(0); // Ordem de exibição
            $table->boolean('ativo')->default(true); // Se o banner está ativo
            $table->date('data_inicio')->nullable(); // Data de início da exibição
            $table->date('data_fim')->nullable(); // Data de fim da exibição
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
