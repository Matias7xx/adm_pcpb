<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Banner extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'titulo',
        'descricao',
        'imagem',
        'link',
        'nova_aba',
        'ordem',
        'ativo',
        'data_inicio',
        'data_fim',
    ];

    protected $casts = [
        'ativo' => 'boolean',
        'nova_aba' => 'boolean',
        'data_inicio' => 'date',
        'data_fim' => 'date',
    ];

    /**
     * Scope para banners ativos
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true)
            ->where(function ($q) {
                $q->whereNull('data_inicio')
                    ->orWhere('data_inicio', '<=', now()->toDateString());
            })
            ->where(function ($q) {
                $q->whereNull('data_fim')
                    ->orWhere('data_fim', '>=', now()->toDateString());
            });
    }

    /**
     * Scope para ordenar por ordem
     */
    public function scopeOrdenados($query)
    {
        return $query->orderBy('ordem')->orderBy('created_at', 'desc');
    }

    /**
     * Obter URL completa da imagem
     */
    public function getImagemUrlAttribute()
    {
        if ($this->imagem) {
            return Storage::disk('public')->url($this->imagem);
        }
        return null;
    }

    /**
     * Verificar se o banner está em período válido
     */
    public function isEmPeriodoValido()
    {
        $hoje = now()->toDateString();

        $inicioValido = !$this->data_inicio || $this->data_inicio <= $hoje;
        $fimValido = !$this->data_fim || $this->data_fim >= $hoje;

        return $inicioValido && $fimValido;
    }

    /**
     * Verificar se pode ser exibido
     */
    public function podeSerExibido()
    {
        return $this->ativo && $this->isEmPeriodoValido();
    }
}
