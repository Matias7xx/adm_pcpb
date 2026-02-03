<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OperacaoRequest extends FormRequest
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
        // Regra de data: só exige after_or_equal:today na criação.
        // Na edição, a operação pode já ter uma data passada válida.
        $regrasData = ['required', 'date'];
        if (!$this->isMethod('PUT') && !$this->isMethod('PATCH')) {
            $regrasData[] = 'after_or_equal:today';
        }

        return [
            // Dados da operação
            'nome_operacao' => ['required', 'string', 'max:255'],
            'autoridade_responsavel_nome' => ['required', 'string', 'max:255'],
            'autoridade_responsavel_matricula' => ['required', 'string', 'max:50'],
            
            // Origem e localização
            'origem_operacao' => ['required', Rule::in(['Nacional', 'Estadual', 'Apoio a outro Estado'])],
            'uf_responsavel' => ['required', 'string', 'size:2', 'uppercase'],
            'data_operacao' => $regrasData,
            
            // Briefing
            'local_briefing' => ['required', 'string', 'max:255'],
            'horario_briefing' => ['required', 'date_format:H:i'],
            
            // Quantidades (devem ser números inteiros não negativos)
            'quantidade_total_alvos' => ['required', 'integer', 'min:0'],
            'quantidade_mandados_prisao' => ['required', 'integer', 'min:0'],
            'quantidade_mandados_busca_apreensao' => ['required', 'integer', 'min:0'],
            'quantidade_mandados_busca_apreensao_infrator' => ['required', 'integer', 'min:0'],
            'quantidade_alvos_outros_estados' => ['required', 'integer', 'min:0'],
            'quantidade_policiais_empregados' => ['required', 'integer', 'min:1'],
            'quantidade_viaturas_empregadas' => ['required', 'integer', 'min:0'],
            
            // Informações da operação
            'cidades_alvo' => ['required', 'string'],
            'crimes_investigados' => ['required', 'string'],
            
            // Vinculações
            'vinculada_unidade' => [
                'required',
                Rule::in([
                    'Delegacia Geral',
                    '1ª SRPC',
                    '2ª SRPC',
                    '3ª SRPC',
                    '4ª SRPC',
                    'COORDEAM',
                    'DIOP'
                ])
            ],
            
            'vinculada_unidade_especializada' => [
                'required',
                Rule::in([
                    'DAV', 'DCCPAT', 'DCCPES', 'DDF', 'DEAM', 'DEATI', 'DEATUR',
                    'DECC', 'DECCOR', 'DECCOT', 'DECHRADI', 'DECON', 'DESARME',
                    'DHE', 'DMA', 'DRACO', 'DRE', 'DRFVC', 'GOE', 'GTE',
                    'DIJ', 'DRCCIJ', 'DRF', 'NH', 'NRQ', 'OUTRA'
                ])
            ],
            
            // Campo opcional - só é obrigatório se unidade especializada for "OUTRA"
            'outra_unidade_policial' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(function () {
                    return $this->vinculada_unidade_especializada === 'OUTRA';
                })
            ],
            
            'vinculada_delegacia_seccional' => [
                'required',
                Rule::in([
                    '1ª DSPC', '2ª DSPC', '3ª DSPC', '4ª DSPC', '5ª DSPC',
                    '6ª DSPC', '7ª DSPC', '8ª DSPC', '9ª DSPC', '10ª DSPC',
                    '11ª DSPC', '12ª DSPC', '13ª DSPC', '14ª DSPC', '15ª DSPC',
                    '16ª DSPC', '17ª DSPC', '18ª DSPC', '19ª DSPC', '20ª DSPC',
                    '21ª DSPC', '22ª DSPC', '23ª DSPC', '24ª DSPC', 'PREJUDICADO'
                ])
            ],
            
            // Solicitação de apoio (opcional)
            'solicitacao_apoio_diop' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'nome_operacao.required' => 'O nome da operação é obrigatório.',
            'autoridade_responsavel_nome.required' => 'O nome da autoridade responsável é obrigatório.',
            'autoridade_responsavel_matricula.required' => 'A matrícula da autoridade responsável é obrigatória.',
            
            'origem_operacao.required' => 'A origem da operação é obrigatória.',
            'origem_operacao.in' => 'Origem da operação inválida.',
            
            'uf_responsavel.required' => 'A UF responsável é obrigatória.',
            'uf_responsavel.size' => 'A UF deve ter exatamente 2 caracteres.',
            
            'data_operacao.required' => 'A data da operação é obrigatória.',
            'data_operacao.date' => 'Data inválida.',
            'data_operacao.after_or_equal' => 'A data da operação não pode ser anterior a hoje.',
            
            'local_briefing.required' => 'O local do briefing é obrigatório.',
            'horario_briefing.required' => 'O horário do briefing é obrigatório.',
            'horario_briefing.date_format' => 'Formato de horário inválido. Use HH:MM.',
            
            'quantidade_total_alvos.required' => 'A quantidade total de alvos é obrigatória.',
            'quantidade_total_alvos.integer' => 'A quantidade total de alvos deve ser um número inteiro.',
            'quantidade_total_alvos.min' => 'A quantidade total de alvos não pode ser negativa.',
            
            'quantidade_mandados_prisao.required' => 'A quantidade de mandados de prisão é obrigatória.',
            'quantidade_mandados_prisao.integer' => 'A quantidade de mandados de prisão deve ser um número inteiro.',
            'quantidade_mandados_prisao.min' => 'A quantidade de mandados de prisão não pode ser negativa.',
            
            'quantidade_mandados_busca_apreensao.required' => 'A quantidade de mandados de busca e apreensão é obrigatória.',
            'quantidade_mandados_busca_apreensao.integer' => 'A quantidade de mandados de busca e apreensão deve ser um número inteiro.',
            'quantidade_mandados_busca_apreensao.min' => 'A quantidade de mandados de busca e apreensão não pode ser negativa.',
            
            'quantidade_mandados_busca_apreensao_infrator.required' => 'A quantidade de mandados de busca e apreensão de infrator é obrigatória.',
            'quantidade_mandados_busca_apreensao_infrator.integer' => 'A quantidade deve ser um número inteiro.',
            'quantidade_mandados_busca_apreensao_infrator.min' => 'A quantidade não pode ser negativa.',
            
            'quantidade_alvos_outros_estados.required' => 'A quantidade de alvos em outros estados é obrigatória.',
            'quantidade_alvos_outros_estados.integer' => 'A quantidade deve ser um número inteiro.',
            'quantidade_alvos_outros_estados.min' => 'A quantidade não pode ser negativa.',
            
            'quantidade_policiais_empregados.required' => 'A quantidade de policiais empregados é obrigatória.',
            'quantidade_policiais_empregados.integer' => 'A quantidade deve ser um número inteiro.',
            'quantidade_policiais_empregados.min' => 'Deve haver pelo menos 1 policial empregado.',
            
            'quantidade_viaturas_empregadas.required' => 'A quantidade de viaturas empregadas é obrigatória.',
            'quantidade_viaturas_empregadas.integer' => 'A quantidade deve ser um número inteiro.',
            'quantidade_viaturas_empregadas.min' => 'A quantidade não pode ser negativa.',
            
            'cidades_alvo.required' => 'As cidades alvo da operação são obrigatórias.',
            'crimes_investigados.required' => 'Os crimes investigados são obrigatórios.',
            
            'vinculada_unidade.required' => 'A unidade vinculada é obrigatória.',
            'vinculada_unidade.in' => 'Unidade vinculada inválida.',
            
            'vinculada_unidade_especializada.required' => 'A unidade especializada vinculada é obrigatória.',
            'vinculada_unidade_especializada.in' => 'Unidade especializada inválida.',
            
            'outra_unidade_policial.required' => 'Especifique a unidade policial quando selecionar "OUTRA".',
            
            'vinculada_delegacia_seccional.required' => 'A delegacia seccional vinculada é obrigatória.',
            'vinculada_delegacia_seccional.in' => 'Delegacia seccional inválida.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('uf_responsavel')) {
            $this->merge([
                'uf_responsavel' => strtoupper($this->uf_responsavel),
            ]);
        }
    }
}