<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResultadoOperacaoRequest extends FormRequest
{
  /**
   * Determine if the user is authorized to make this request.
   */
  public function authorize(): bool
  {
    return auth()->check();
  }

  /**
   * Get the validation rules that apply to the request.
   */
  public function rules(): array
  {
    return [
      // ID da operação
      'operacao_id' => ['required', 'exists:operacoes,id'],

      // Autoridade e Policial Responsáveis (editáveis)
      'autoridade_responsavel_nome' => ['required', 'string', 'max:255'],
      'autoridade_responsavel_matricula' => ['required', 'string', 'max:50'],

      // Número do processo PJE (opcional)
      'numero_processo_pje' => ['nullable', 'string', 'max:255'],

      // Mandados de prisão
      'mandados_prisao_cumpridos' => ['required', 'integer', 'min:0'],
      'mandados_prisao_cumpridos_detalhes' => ['required', 'string'],
      'mandados_prisao_nao_cumpridos' => ['required', 'integer', 'min:0'],

      // Mandados de busca e apreensão
      'mandados_busca_cumpridos' => ['required', 'integer', 'min:0'],
      'mandados_busca_infrator_cumpridos' => ['required', 'integer', 'min:0'],
      'mandados_busca_infrator_nao_cumpridos' => [
        'required',
        'integer',
        'min:0',
      ],

      // Prisões em flagrante
      'prisoes_flagrante' => ['required', 'integer', 'min:0'],

      'quantidade_armas_apreendidas' => ['required', 'integer', 'min:0'],
      'tipo_arma_apreendida' => ['required', 'array', 'min:1'], // Sempre exige pelo menos 1 (NENHUMA ou outro tipo)
      'tipo_arma_apreendida.*' => [
        'required',
        'string',
        Rule::in([
          'NENHUMA',
          'REVÓLVER',
          'PISTOLA',
          'ESPINGARDA',
          'FUZIL',
          'ARMA ARTESANAL',
          'EXPLOSIVO',
          'PREJUDICADO',
        ]),
      ],
      'detalhes_armas_apreendidas' => [
        'required',
        'string',
        // Aceita "N/A" quando não houver armas
      ],

      // Munições
      'municoes_apreendidas' => [
        'required',
        'string',
        // Aceita "N/A" quando não houver armas
      ],

      'entorpecente_apreendido' => ['required', 'array', 'min:1'], // Sempre exige pelo menos 1 (NENHUM ou outro tipo)
      'entorpecente_apreendido.*' => [
        'required',
        'string',
        Rule::in([
          'NENHUM',
          'MACONHA',
          'COCAINA',
          'CRACK',
          'SKANK',
          'HEROINA',
          'LSD',
          'ECSTASY (MDMA)',
          'OUTROS',
        ]),
      ],
      'detalhes_entorpecentes' => [
        Rule::requiredIf(function () {
          $entorpecentes = $this->entorpecente_apreendido ?? [];
          // Requer detalhes se tiver qualquer entorpecente EXCETO "NENHUM"
          return !empty($entorpecentes) && !in_array('NENHUM', $entorpecentes);
        }),
        'nullable',
        'string',
      ],

      // Valores e objetos apreendidos
      'valores_dinheiro' => ['required', 'numeric', 'min:0'],
      'veiculos_apreendidos' => ['required', 'string'],
      'demais_objetos_apreendidos' => ['required', 'string'],

      // Outras informações (opcional)
      'outras_informacoes' => ['nullable', 'string'],
    ];
  }

  /**
   * Get custom messages for validator errors.
   */
  public function messages(): array
  {
    return [
      'operacao_id.required' => 'A operação é obrigatória.',
      'operacao_id.exists' => 'Operação não encontrada.',

      'autoridade_responsavel_nome.required' =>
        'O nome da autoridade responsável é obrigatório.',
      'autoridade_responsavel_matricula.required' =>
        'A matrícula da autoridade responsável é obrigatória.',

      'mandados_prisao_cumpridos.required' =>
        'A quantidade de mandados de prisão cumpridos é obrigatória.',
      'mandados_prisao_cumpridos.integer' =>
        'A quantidade deve ser um número inteiro.',
      'mandados_prisao_cumpridos.min' => 'A quantidade não pode ser negativa.',

      'mandados_prisao_cumpridos_detalhes.required' =>
        'Informe os detalhes dos presos ou "N/A" se não houver.',

      'mandados_prisao_nao_cumpridos.required' =>
        'A quantidade de mandados de prisão não cumpridos é obrigatória.',
      'mandados_prisao_nao_cumpridos.integer' =>
        'A quantidade deve ser um número inteiro.',
      'mandados_prisao_nao_cumpridos.min' =>
        'A quantidade não pode ser negativa.',

      'mandados_busca_cumpridos.required' =>
        'A quantidade de mandados de busca cumpridos é obrigatória.',
      'mandados_busca_cumpridos.integer' =>
        'A quantidade deve ser um número inteiro.',
      'mandados_busca_cumpridos.min' => 'A quantidade não pode ser negativa.',

      'mandados_busca_infrator_cumpridos.required' =>
        'A quantidade de mandados de busca de infrator cumpridos é obrigatória.',
      'mandados_busca_infrator_cumpridos.integer' =>
        'A quantidade deve ser um número inteiro.',
      'mandados_busca_infrator_cumpridos.min' =>
        'A quantidade não pode ser negativa.',

      'mandados_busca_infrator_nao_cumpridos.required' =>
        'A quantidade de mandados de busca de infrator não cumpridos é obrigatória.',
      'mandados_busca_infrator_nao_cumpridos.integer' =>
        'A quantidade deve ser um número inteiro.',
      'mandados_busca_infrator_nao_cumpridos.min' =>
        'A quantidade não pode ser negativa.',

      'prisoes_flagrante.required' =>
        'A quantidade de prisões em flagrante é obrigatória.',
      'prisoes_flagrante.integer' => 'A quantidade deve ser um número inteiro.',
      'prisoes_flagrante.min' => 'A quantidade não pode ser negativa.',

      'quantidade_armas_apreendidas.required' =>
        'A quantidade de armas apreendidas é obrigatória.',
      'quantidade_armas_apreendidas.integer' =>
        'A quantidade deve ser um número inteiro.',
      'quantidade_armas_apreendidas.min' =>
        'A quantidade não pode ser negativa.',

      'tipo_arma_apreendida.required' =>
        'Selecione pelo menos um tipo de arma.',
      'tipo_arma_apreendida.array' => 'Os tipos de arma devem ser um array.',
      'tipo_arma_apreendida.min' =>
        'Selecione pelo menos um tipo de arma (ou marque "Nenhuma").',
      'tipo_arma_apreendida.*.required' => 'Cada tipo de arma é obrigatório.',
      'tipo_arma_apreendida.*.in' => 'Tipo de arma inválido.',

      'detalhes_armas_apreendidas.required' =>
        'Informe os detalhes das armas ou "N/A" se não houver.',

      'municoes_apreendidas.required' =>
        'Informe as munições apreendidas ou "N/A" se não houver.',

      'entorpecente_apreendido.required' =>
        'Selecione pelo menos uma opção de entorpecente.',
      'entorpecente_apreendido.array' => 'Os entorpecentes devem ser um array.',
      'entorpecente_apreendido.min' =>
        'Selecione pelo menos uma opção (ou marque "Nenhum").',
      'entorpecente_apreendido.*.required' =>
        'Cada tipo de entorpecente é obrigatório.',
      'entorpecente_apreendido.*.in' => 'Tipo de entorpecente inválido.',

      'detalhes_entorpecentes.required' =>
        'Especifique o peso/quantidade dos entorpecentes quando houver apreensão.',

      'valores_dinheiro.required' =>
        'O valor em dinheiro é obrigatório (informar 0 se não houver).',
      'valores_dinheiro.numeric' => 'O valor deve ser numérico.',
      'valores_dinheiro.min' => 'O valor não pode ser negativo.',

      'veiculos_apreendidos.required' =>
        'Informe os veículos apreendidos ou "N/A" se não houver.',

      'demais_objetos_apreendidos.required' =>
        'Informe os objetos apreendidos ou "N/A" se não houver.',
    ];
  }
}
