<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import Header from '../Components/Header.vue';
import SiteNavbar from '../Components/SiteNavbar.vue';
import Footer from '../Components/Footer.vue';

const props = defineProps({
  operacao: Object,
  operacoesDisponiveis: Array,
  opcoes: Object,
});

// Etapas do formulário
const etapaAtual = ref(1);
const totalEtapas = 5;
const houveApreensaoArmas = ref(false);

// Formulário
const form = useForm({
  operacao_id: props.operacao?.id || null,

  // Autoridade e Policial (editáveis)
  autoridade_responsavel_nome:
    props.operacao?.autoridade_responsavel_nome || '',
  autoridade_responsavel_matricula: String(
    props.operacao?.autoridade_responsavel_matricula || ''
  ),
  // policial_responsavel_nome: props.operacao?.policial_responsavel_nome || '',
  // policial_responsavel_matricula: props.operacao?.policial_responsavel_matricula || '',

  numero_processo_pje: '',

  // Mandados
  mandados_prisao_cumpridos: 0,
  mandados_prisao_cumpridos_detalhes: '',
  mandados_prisao_nao_cumpridos: 0,
  mandados_busca_cumpridos: 0,
  mandados_busca_infrator_cumpridos: 0,
  mandados_busca_infrator_nao_cumpridos: 0,

  // Prisões
  prisoes_flagrante: 0,

  // Armas - AGORA É ARRAY
  quantidade_armas_apreendidas: 0,
  tipo_arma_apreendida: [],
  detalhes_armas_apreendidas: '',

  // Munições
  municoes_apreendidas: '',

  // Entorpecentes - AGORA É ARRAY
  entorpecente_apreendido: [],
  detalhes_entorpecentes: '',

  // Valores e objetos
  valores_dinheiro: 0,
  valores_dinheiro_formatado: 'R$ 0,00', // Máscara
  veiculos_apreendidos: '',
  demais_objetos_apreendidos: '',

  // Outras informações
  outras_informacoes: '',
});

//  Bloquear texto em campos numéricos
const apenasNumeros = event => {
  const char = String.fromCharCode(event.keyCode);
  if (!/^[0-9]$/.test(char)) {
    event.preventDefault();
  }
};

// Formatar dinheiro
const formatarDinheiro = valor => {
  if (valor === null || valor === undefined || valor === '') return 'R$ 0,00';
  const numero = typeof valor === 'string' ? parseFloat(valor) : valor;
  if (isNaN(numero)) return 'R$ 0,00';
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  }).format(numero);
};

// Atualizar formatação
watch(
  () => form.valores_dinheiro,
  novoValor => {
    form.valores_dinheiro_formatado = formatarDinheiro(novoValor);
  }
);

// Watch: Controlar armas
watch(houveApreensaoArmas, novoValor => {
  if (novoValor === false) {
    form.tipo_arma_apreendida = ['NENHUMA'];
    form.quantidade_armas_apreendidas = 0;
    form.detalhes_armas_apreendidas = 'N/A';
    form.municoes_apreendidas = 'N/A';
  } else {
    form.tipo_arma_apreendida = [];
    form.detalhes_armas_apreendidas = '';
    form.municoes_apreendidas = '';
  }
});

// Quando seleciona uma operação, preenche autoridade e policial
watch(
  () => form.operacao_id,
  novoId => {
    if (novoId && props.operacoesDisponiveis) {
      const opSelecionada = props.operacoesDisponiveis.find(
        op => op.id === novoId
      );
      if (opSelecionada) {
        form.autoridade_responsavel_nome =
          opSelecionada.autoridade_responsavel_nome || '';
        form.autoridade_responsavel_matricula = String(
          opSelecionada.autoridade_responsavel_matricula || ''
        );
        // form.policial_responsavel_nome = opSelecionada.policial_responsavel_nome || '';
        // form.policial_responsavel_matricula = opSelecionada.policial_responsavel_matricula || '';
      }
    }
  }
);

const operacaoSelecionada = computed(() => {
  if (!form.operacao_id) return null;
  return props.operacao;
});

const formatarData = data => {
  if (!data) return '';
  return new Date(data).toLocaleDateString('pt-BR');
};

const nenhumaArmaMarcada = computed(() => {
  return form.tipo_arma_apreendida.includes('NENHUMA');
});

const algumaArmaMarcada = computed(() => {
  return form.tipo_arma_apreendida.some(tipo => tipo !== 'NENHUMA');
});

const nenhumEntorpecenteMarcado = computed(() => {
  return form.entorpecente_apreendido.includes('NENHUM');
});

const algumEntorpecenteMarcado = computed(() => {
  return form.entorpecente_apreendido.some(tipo => tipo !== 'NENHUM');
});

// Watch: Desmarcar outras armas quando marcar NENHUMA
watch(
  () => form.tipo_arma_apreendida,
  novoValor => {
    if (novoValor.includes('NENHUMA') && novoValor.length > 1) {
      // Se marcou NENHUMA, manter só NENHUMA
      form.tipo_arma_apreendida = ['NENHUMA'];
    }

    // Se marcou NENHUMA, preencher automaticamente os campos obrigatórios
    if (novoValor.includes('NENHUMA')) {
      form.quantidade_armas_apreendidas = 0;
      form.detalhes_armas_apreendidas = 'N/A';
      form.municoes_apreendidas = 'N/A';
    }
  }
);

// Watch: Desmarcar outros entorpecentes quando marcar NÃO
watch(
  () => form.entorpecente_apreendido,
  novoValor => {
    if (novoValor.includes('NENHUM') && novoValor.length > 1) {
      // Se marcou NÃO, manter só NÃO
      form.entorpecente_apreendido = ['NENHUM'];
    }

    // Se marcou NENHUM, limpar detalhes (não é obrigatório quando não há entorpecentes)
    if (novoValor.includes('NENHUM')) {
      form.detalhes_entorpecentes = '';
    }
  }
);

// Validação por etapa
const errorsEtapa = ref({});

// Helper para validar campos numéricos (verifica se está vazio OU negativo)
const campoNumericoInvalido = valor => {
  return (
    valor === null ||
    valor === undefined ||
    valor === '' ||
    isNaN(valor) ||
    valor < 0
  );
};

const validarEtapa = etapa => {
  const erros = {};

  if (etapa === 1) {
    if (!form.operacao_id) erros.operacao_id = 'Selecione uma operação.';
    if (!form.autoridade_responsavel_nome?.trim())
      erros.autoridade_responsavel_nome = 'Nome da autoridade é obrigatório.';
    if (!String(form.autoridade_responsavel_matricula || '').trim())
      erros.autoridade_responsavel_matricula =
        'Matrícula da autoridade é obrigatória.';
  }

  if (etapa === 2) {
    // Validação robusta: verifica se está vazio E se é negativo
    if (campoNumericoInvalido(form.mandados_prisao_cumpridos)) {
      erros.mandados_prisao_cumpridos =
        'Este campo é obrigatório (use 0 se não houver).';
    }
    if (!form.mandados_prisao_cumpridos_detalhes?.trim()) {
      erros.mandados_prisao_cumpridos_detalhes = 'Informe os detalhes ou N/A.';
    }
    if (campoNumericoInvalido(form.mandados_prisao_nao_cumpridos)) {
      erros.mandados_prisao_nao_cumpridos =
        'Este campo é obrigatório (use 0 se não houver).';
    }
    if (campoNumericoInvalido(form.mandados_busca_cumpridos)) {
      erros.mandados_busca_cumpridos =
        'Este campo é obrigatório (use 0 se não houver).';
    }
    if (campoNumericoInvalido(form.mandados_busca_infrator_cumpridos)) {
      erros.mandados_busca_infrator_cumpridos =
        'Este campo é obrigatório (use 0 se não houver).';
    }
    if (campoNumericoInvalido(form.mandados_busca_infrator_nao_cumpridos)) {
      erros.mandados_busca_infrator_nao_cumpridos =
        'Este campo é obrigatório (use 0 se não houver).';
    }
    if (campoNumericoInvalido(form.prisoes_flagrante)) {
      erros.prisoes_flagrante =
        'Este campo é obrigatório (use 0 se não houver).';
    }
  }

  if (etapa === 3) {
    if (houveApreensaoArmas.value === false) {
      if (!form.tipo_arma_apreendida.includes('NENHUMA')) {
        form.tipo_arma_apreendida = ['NENHUMA'];
      }
    } else {
      if (campoNumericoInvalido(form.quantidade_armas_apreendidas)) {
        erros.quantidade_armas_apreendidas = 'Este campo é obrigatório.';
      }
      if (form.tipo_arma_apreendida.length === 0) {
        erros.tipo_arma_apreendida = 'Selecione pelo menos um tipo.';
      }
      if (!form.detalhes_armas_apreendidas?.trim()) {
        erros.detalhes_armas_apreendidas = 'Informe os detalhes.';
      }
      if (!form.municoes_apreendidas?.trim()) {
        erros.municoes_apreendidas = 'Informe as munições ou N/A.';
      }
    }
  }

  if (etapa === 4) {
    if (form.entorpecente_apreendido.length === 0) {
      erros.entorpecente_apreendido = 'Selecione pelo menos uma opção.';
    }
    if (
      !form.entorpecente_apreendido.includes('NENHUM') &&
      form.entorpecente_apreendido.length > 0 &&
      !form.detalhes_entorpecentes?.trim()
    ) {
      erros.detalhes_entorpecentes = 'Informe os detalhes dos entorpecentes.';
    }
  }

  if (etapa === 5) {
    // Validação robusta para valores em dinheiro
    if (campoNumericoInvalido(form.valores_dinheiro)) {
      erros.valores_dinheiro =
        'Este campo é obrigatório (use 0 se não houver).';
    }
    if (!form.veiculos_apreendidos?.trim()) {
      erros.veiculos_apreendidos = 'Informe os veículos ou N/A.';
    }
    if (!form.demais_objetos_apreendidos?.trim()) {
      erros.demais_objetos_apreendidos = 'Informe os objetos ou N/A.';
    }
  }

  return erros;
};

const proximaEtapa = () => {
  const erros = validarEtapa(etapaAtual.value);
  errorsEtapa.value = erros;

  if (Object.keys(erros).length > 0) return;

  if (etapaAtual.value < totalEtapas) {
    etapaAtual.value++;
  }
};

const voltarEtapa = () => {
  errorsEtapa.value = {};
  if (etapaAtual.value > 1) {
    etapaAtual.value--;
  }
};

const submit = () => {
  console.log('=== INICIANDO SUBMIT ===');
  console.log('Etapa atual:', etapaAtual.value);
  console.log('Dados do form:', form.data());

  // Validar a etapa atual antes de enviar
  const erros = validarEtapa(etapaAtual.value);
  errorsEtapa.value = erros;

  console.log('Erros de validação:', erros);
  console.log('Total de erros:', Object.keys(erros).length);

  // Se houver erros, não enviar
  if (Object.keys(erros).length > 0) {
    console.warn('SUBMIT BLOQUEADO - Há erros de validação!');
    // Scroll para o primeiro erro
    const primeiroErro = Object.keys(erros)[0];
    console.log('Primeiro erro:', primeiroErro, '-', erros[primeiroErro]);
    return;
  }

  // Garantir que matrícula seja string antes de enviar
  form.autoridade_responsavel_matricula = String(
    form.autoridade_responsavel_matricula || ''
  );

  // GARANTIR que quando NÃO houve armas, os campos estejam preenchidos
  if (
    houveApreensaoArmas.value === false ||
    form.tipo_arma_apreendida.includes('NENHUMA')
  ) {
    form.tipo_arma_apreendida = ['NENHUMA'];
    form.quantidade_armas_apreendidas = 0;
    form.detalhes_armas_apreendidas = 'N/A';
    form.municoes_apreendidas = 'N/A';
    console.log('Sem armas - Campos preenchidos com N/A');
  }

  console.log('Validação OK - Enviando para o servidor...');
  console.log(
    'Matrícula (string):',
    form.autoridade_responsavel_matricula,
    typeof form.autoridade_responsavel_matricula
  );
  console.log('Tipo arma:', form.tipo_arma_apreendida);
  console.log('Detalhes armas:', form.detalhes_armas_apreendidas);
  console.log('Munições:', form.municoes_apreendidas);

  form.post(route('resultados-operacao.store'), {
    preserveScroll: true,
    onSuccess: () => {
      console.log('Sucesso!');
    },
    onError: errors => {
      console.error('Erros do servidor:', errors);
    },
    onFinish: () => {
      console.log('=== SUBMIT FINALIZADO ===');
    },
  });
};

const erro = campo => {
  return form.errors[campo] || errorsEtapa.value[campo];
};
</script>

<template>
  <div>
    <Head>
      <title>Cadastrar Resultado da Operação (Debriefing)</title>
    </Head>

    <Header />
    <SiteNavbar />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
      <Link
        :href="route('operacoes.index')"
        class="inline-flex items-center text-sm font-medium text-[#bea55a] hover:text-[#968143] transition-colors group"
      >
        <svg
          class="w-5 h-5 mr-2 transform group-hover:-translate-x-1 transition-transform"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M10 19l-7-7m0 0l7-7m-7 7h18"
          />
        </svg>
        Voltar para a Listagem
      </Link>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div
        class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100"
      >
        <!-- Cabeçalho -->
        <div class="bg-white shadow rounded-lg mb-6 p-6">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h1 class="text-3xl font-bold text-gray-900">
                Cadastrar Resultado da Operação
              </h1>
              <p class="text-gray-600 mt-2">
                Preencha os dados do debriefing da operação realizada
              </p>
            </div>
            <div class="text-right">
              <span class="text-sm font-medium text-gray-500">Etapa</span>
              <div class="text-2xl font-bold text-[#bea55a]">
                {{ etapaAtual }} / {{ totalEtapas }}
              </div>
            </div>
          </div>

          <!-- Progress Bar -->
          <div class="mt-6">
            <div class="flex items-center justify-between mb-2">
              <span
                v-for="i in totalEtapas"
                :key="i"
                class="text-xs font-medium"
                :class="etapaAtual >= i ? 'text-[#bea55a]' : 'text-gray-400'"
              >
                {{
                  i === 1
                    ? 'Identificação'
                    : i === 2
                      ? 'Mandados'
                      : i === 3
                        ? 'Armas'
                        : i === 4
                          ? 'Entorpecentes'
                          : 'Valores'
                }}
              </span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div
                class="bg-[#bea55a] h-2 rounded-full transition-all duration-300"
                :style="{ width: `${(etapaAtual / totalEtapas) * 100}%` }"
              ></div>
            </div>
          </div>
        </div>

        <!-- Formulário -->
        <form @submit.prevent="submit" novalidate>
          <div class="bg-white shadow rounded-lg p-6">
            <!-- ETAPA 1: Identificação -->
            <div v-show="etapaAtual === 1" class="space-y-6">
              <h2 class="text-xl font-semibold text-gray-800 border-b pb-3">
                Identificação da Operação e Responsáveis
              </h2>

              <!-- Seleção da Operação (se não veio do Show) -->
              <div v-if="!operacao">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Selecione a Operação <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.operacao_id"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#bea55a] focus:border-transparent"
                  :class="
                    erro('operacao_id') ? 'border-red-500' : 'border-gray-300'
                  "
                >
                  <option :value="null">Selecione uma operação...</option>
                  <option
                    v-for="op in operacoesDisponiveis"
                    :key="op.id"
                    :value="op.id"
                  >
                    {{ op.label }}
                  </option>
                </select>
                <p v-if="erro('operacao_id')" class="mt-1 text-sm text-red-600">
                  {{ erro('operacao_id') }}
                </p>
              </div>

              <!-- Dados da Operação (Readonly) -->
              <div
                v-if="operacaoSelecionada || operacao"
                class="bg-gray-50 border-2 border-gray-200 rounded-lg p-6"
              >
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                  Dados da Operação Cadastrada
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div class="bg-white p-3 rounded border border-gray-200">
                    <label
                      class="text-xs font-semibold text-gray-700 uppercase"
                    >
                      Nome da Operação</label
                    >
                    <p class="text-gray-900 font-medium mt-1">
                      {{ operacao?.nome_operacao || 'Carregando...' }}
                    </p>
                  </div>

                  <div class="bg-white p-3 rounded border border-gray-200">
                    <label
                      class="text-xs font-semibold text-gray-700 uppercase"
                    >
                      Data da Deflagração</label
                    >
                    <p class="text-gray-900 font-medium mt-1">
                      {{
                        operacao?.data_operacao_formatada ||
                        formatarData(operacao?.data_operacao) ||
                        'Carregando...'
                      }}
                    </p>
                  </div>

                  <div class="bg-white p-3 rounded border border-gray-200">
                    <label
                      class="text-xs font-semibold text-gray-700 uppercase"
                    >
                      Unidade Solicitante/Executora Vinculada à</label
                    >
                    <p class="text-gray-900 font-medium mt-1">
                      {{ operacao?.vinculada_unidade || 'Carregando...' }}
                    </p>
                  </div>

                  <div class="bg-white p-3 rounded border border-gray-200">
                    <label
                      class="text-xs font-semibold text-gray-700 uppercase"
                    >
                      Unidade Solicitante/Executora</label
                    >
                    <p class="text-gray-900 font-medium mt-1">
                      {{
                        operacao?.vinculada_unidade_especializada ||
                        'Carregando...'
                      }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- 2. Número do Processo PJE (Opcional) -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Número do Processo PJE
                  <span class="text-gray-500 text-xs"
                    >(Opcional - "PREJUDICADO" se nacional)</span
                  >
                </label>
                <input
                  v-model="form.numero_processo_pje"
                  type="text"
                  placeholder="Ex: 0000000-00.0000.0.00.0000 ou PREJUDICADO"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#bea55a] focus:border-transparent"
                  :class="
                    erro('numero_processo_pje')
                      ? 'border-red-500'
                      : 'border-gray-300'
                  "
                />
                <p
                  v-if="erro('numero_processo_pje')"
                  class="mt-1 text-sm text-red-600"
                >
                  {{ erro('numero_processo_pje') }}
                </p>
              </div>

              <!-- 3-4. Autoridade Responsável (EDITÁVEL) -->
              <div class="bg-gray-50 border-2 border-gray-300 rounded-lg p-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">
                  Autoridade Policial Responsável pelo Resultado
                </h3>
                <p class="text-sm text-gray-700 mb-4">
                  Por padrão, usa a mesma autoridade informada no cadastro da
                  operação. Edite se for diferente.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Nome da Autoridade <span class="text-red-500">*</span>
                    </label>
                    <input
                      v-model="form.autoridade_responsavel_nome"
                      type="text"
                      class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#bea55a] focus:border-transparent"
                      :class="
                        erro('autoridade_responsavel_nome')
                          ? 'border-red-500'
                          : 'border-gray-300'
                      "
                    />
                    <p
                      v-if="erro('autoridade_responsavel_nome')"
                      class="mt-1 text-sm text-red-600"
                    >
                      {{ erro('autoridade_responsavel_nome') }}
                    </p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Matrícula da Autoridade
                      <span class="text-red-500">*</span>
                    </label>
                    <input
                      v-model="form.autoridade_responsavel_matricula"
                      type="number"
                      @keypress="apenasNumeros"
                      class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#bea55a] focus:border-transparent"
                      :class="
                        erro('autoridade_responsavel_matricula')
                          ? 'border-red-500'
                          : 'border-gray-300'
                      "
                    />
                    <p
                      v-if="erro('autoridade_responsavel_matricula')"
                      class="mt-1 text-sm text-red-600"
                    >
                      {{ erro('autoridade_responsavel_matricula') }}
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- ETAPA 2: Mandados e Prisões -->
            <div v-show="etapaAtual === 2" class="space-y-6">
              <h2 class="text-xl font-semibold text-gray-800 border-b pb-3">
                Mandados Cumpridos e Prisões
              </h2>

              <!-- 5. Mandados de Prisão Cumpridos -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Quantidade de Mandados de Prisão Cumpridos
                  <span class="text-red-500">*</span>
                </label>
                <input
                  v-model.number="form.mandados_prisao_cumpridos"
                  type="number"
                  min="0"
                  @keypress="apenasNumeros"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#bea55a] focus:border-transparent"
                  :class="
                    erro('mandados_prisao_cumpridos')
                      ? 'border-red-500'
                      : 'border-gray-300'
                  "
                />
                <p
                  v-if="erro('mandados_prisao_cumpridos')"
                  class="mt-1 text-sm text-red-600"
                >
                  {{ erro('mandados_prisao_cumpridos') }}
                </p>
              </div>

              <!-- 6. Detalhes dos Presos -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Detalhes dos Presos (Nome e CPF)
                  <span class="text-red-500">*</span>
                </label>
                <textarea
                  v-model="form.mandados_prisao_cumpridos_detalhes"
                  rows="4"
                  placeholder="João da Silva - CPF 123.456.789-00&#10;Maria Santos - CPF 987.654.321-00&#10;&#10;Se não houver, informar: N/A"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#bea55a] focus:border-transparent"
                  :class="
                    erro('mandados_prisao_cumpridos_detalhes')
                      ? 'border-red-500'
                      : 'border-gray-300'
                  "
                ></textarea>
                <p class="mt-1 text-xs text-gray-500">
                  Liste um preso por linha. Se não houver, informe "N/A".
                </p>
                <p
                  v-if="erro('mandados_prisao_cumpridos_detalhes')"
                  class="mt-1 text-sm text-red-600"
                >
                  {{ erro('mandados_prisao_cumpridos_detalhes') }}
                </p>
              </div>

              <!-- 7-11. Outros Mandados -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Mandados de Prisão Não Cumpridos
                    <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model.number="form.mandados_prisao_nao_cumpridos"
                    type="number"
                    min="0"
                    @keypress="apenasNumeros"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#bea55a] focus:border-transparent"
                    :class="
                      erro('mandados_prisao_nao_cumpridos')
                        ? 'border-red-500'
                        : 'border-gray-300'
                    "
                    required
                  />
                  <p
                    v-if="erro('mandados_prisao_nao_cumpridos')"
                    class="mt-1 text-sm text-red-600"
                  >
                    {{ erro('mandados_prisao_nao_cumpridos') }}
                  </p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Mandados de Busca Cumpridos
                    <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model.number="form.mandados_busca_cumpridos"
                    type="number"
                    min="0"
                    @keypress="apenasNumeros"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#bea55a] focus:border-transparent"
                    :class="
                      erro('mandados_busca_cumpridos')
                        ? 'border-red-500'
                        : 'border-gray-300'
                    "
                  />
                  <p
                    v-if="erro('mandados_busca_cumpridos')"
                    class="mt-1 text-sm text-red-600"
                  >
                    {{ erro('mandados_busca_cumpridos') }}
                  </p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Mandados de Busca de Infrator Cumpridos
                    <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model.number="form.mandados_busca_infrator_cumpridos"
                    type="number"
                    min="0"
                    @keypress="apenasNumeros"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#bea55a] focus:border-transparent"
                    :class="
                      erro('mandados_busca_infrator_cumpridos')
                        ? 'border-red-500'
                        : 'border-gray-300'
                    "
                  />
                  <p
                    v-if="erro('mandados_busca_infrator_cumpridos')"
                    class="mt-1 text-sm text-red-600"
                  >
                    {{ erro('mandados_busca_infrator_cumpridos') }}
                  </p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Mandados de Busca de Infrator Não Cumpridos
                    <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model.number="form.mandados_busca_infrator_nao_cumpridos"
                    type="number"
                    min="0"
                    @keypress="apenasNumeros"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#bea55a] focus:border-transparent"
                    :class="
                      erro('mandados_busca_infrator_nao_cumpridos')
                        ? 'border-red-500'
                        : 'border-gray-300'
                    "
                  />
                  <p
                    v-if="erro('mandados_busca_infrator_nao_cumpridos')"
                    class="mt-1 text-sm text-red-600"
                  >
                    {{ erro('mandados_busca_infrator_nao_cumpridos') }}
                  </p>
                </div>
              </div>

              <!-- 18. Prisões em Flagrante -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Quantidade de Prisões em Flagrante
                  <span class="text-red-500">*</span>
                </label>
                <input
                  v-model.number="form.prisoes_flagrante"
                  type="number"
                  min="0"
                  @keypress="apenasNumeros"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#bea55a] focus:border-transparent"
                  :class="
                    erro('prisoes_flagrante')
                      ? 'border-red-500'
                      : 'border-gray-300'
                  "
                />
                <p
                  v-if="erro('prisoes_flagrante')"
                  class="mt-1 text-sm text-red-600"
                >
                  {{ erro('prisoes_flagrante') }}
                </p>
              </div>
            </div>

            <!-- ETAPA 3: Armas Apreendidas -->
            <div v-show="etapaAtual === 3" class="space-y-6">
              <h2 class="text-xl font-semibold text-gray-800 border-b pb-3">
                Armas e Munições Apreendidas
              </h2>

              <!-- PERGUNTA INICIAL -->
              <div class="bg-gray-50 border-2 border-gray-200 rounded-lg p-6">
                <label class="block text-lg font-semibold text-gray-900 mb-4">
                  Alguma arma foi apreendida nesta operação?
                  <span class="text-red-500">*</span>
                </label>

                <div class="flex gap-6">
                  <label class="flex items-center cursor-pointer">
                    <input
                      type="radio"
                      :value="false"
                      v-model="houveApreensaoArmas"
                      class="w-5 h-5 text-[#bea55a] border-gray-300 focus:ring-[#bea55a]"
                    />
                    <span class="ml-3 text-base font-medium text-gray-900"
                      >Não</span
                    >
                  </label>

                  <label class="flex items-center cursor-pointer">
                    <input
                      type="radio"
                      :value="true"
                      v-model="houveApreensaoArmas"
                      class="w-5 h-5 text-[#bea55a] border-gray-300 focus:ring-[#bea55a]"
                    />
                    <span class="ml-3 text-base font-medium text-gray-900"
                      >Sim</span
                    >
                  </label>
                </div>
              </div>

              <!-- CAMPOS (só se SIM) -->
              <div
                v-if="houveApreensaoArmas"
                class="space-y-6 border-2 border-gray-200 bg-gray-50 rounded-lg p-6"
              >
                <!-- Quantidade -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Quantidade Total de Armas
                    <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model.number="form.quantidade_armas_apreendidas"
                    type="number"
                    min="0"
                    @keypress="apenasNumeros"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#bea55a] focus:border-transparent"
                    :class="
                      erro('quantidade_armas_apreendidas')
                        ? 'border-red-500'
                        : 'border-gray-300'
                    "
                  />
                  <p
                    v-if="erro('quantidade_armas_apreendidas')"
                    class="mt-1 text-sm text-red-600"
                  >
                    {{ erro('quantidade_armas_apreendidas') }}
                  </p>
                </div>

                <!-- Tipos -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-3">
                    Tipos de Arma <span class="text-red-500">*</span>
                  </label>

                  <div
                    class="grid grid-cols-1 md:grid-cols-2 gap-3 bg-white p-4 rounded-lg border"
                  >
                    <div class="flex items-center">
                      <input
                        type="checkbox"
                        id="arma_revolver"
                        value="REVÓLVER"
                        v-model="form.tipo_arma_apreendida"
                        class="w-4 h-4 text-[#bea55a] border-gray-300 rounded focus:ring-[#bea55a]"
                      />
                      <label
                        for="arma_revolver"
                        class="ml-2 text-sm text-gray-700 cursor-pointer"
                        >Revólver</label
                      >
                    </div>

                    <div class="flex items-center">
                      <input
                        type="checkbox"
                        id="arma_pistola"
                        value="PISTOLA"
                        v-model="form.tipo_arma_apreendida"
                        class="w-4 h-4 text-[#bea55a] border-gray-300 rounded focus:ring-[#bea55a]"
                      />
                      <label
                        for="arma_pistola"
                        class="ml-2 text-sm text-gray-700 cursor-pointer"
                        >Pistola</label
                      >
                    </div>

                    <div class="flex items-center">
                      <input
                        type="checkbox"
                        id="arma_espingarda"
                        value="ESPINGARDA"
                        v-model="form.tipo_arma_apreendida"
                        class="w-4 h-4 text-[#bea55a] border-gray-300 rounded focus:ring-[#bea55a]"
                      />
                      <label
                        for="arma_espingarda"
                        class="ml-2 text-sm text-gray-700 cursor-pointer"
                        >Espingarda</label
                      >
                    </div>

                    <div class="flex items-center">
                      <input
                        type="checkbox"
                        id="arma_fuzil"
                        value="FUZIL"
                        v-model="form.tipo_arma_apreendida"
                        class="w-4 h-4 text-[#bea55a] border-gray-300 rounded focus:ring-[#bea55a]"
                      />
                      <label
                        for="arma_fuzil"
                        class="ml-2 text-sm text-gray-700 cursor-pointer"
                        >Fuzil</label
                      >
                    </div>

                    <div class="flex items-center">
                      <input
                        type="checkbox"
                        id="arma_artesanal"
                        value="ARMA ARTESANAL"
                        v-model="form.tipo_arma_apreendida"
                        class="w-4 h-4 text-[#bea55a] border-gray-300 rounded focus:ring-[#bea55a]"
                      />
                      <label
                        for="arma_artesanal"
                        class="ml-2 text-sm text-gray-700 cursor-pointer"
                        >Arma Artesanal</label
                      >
                    </div>

                    <div class="flex items-center">
                      <input
                        type="checkbox"
                        id="arma_explosivo"
                        value="EXPLOSIVO"
                        v-model="form.tipo_arma_apreendida"
                        class="w-4 h-4 text-[#bea55a] border-gray-300 rounded focus:ring-[#bea55a]"
                      />
                      <label
                        for="arma_explosivo"
                        class="ml-2 text-sm text-gray-700 cursor-pointer"
                        >Explosivo</label
                      >
                    </div>

                    <!-- <div class="flex items-center">
                      <input type="checkbox" id="arma_prejudicado" value="PREJUDICADO"
                        v-model="form.tipo_arma_apreendida"
                        class="w-4 h-4 text-[#bea55a] border-gray-300 rounded focus:ring-[#bea55a]"
                      />
                      <label for="arma_prejudicado" class="ml-2 text-sm text-gray-700 cursor-pointer">Prejudicado</label>
                    </div> -->
                  </div>

                  <p class="mt-2 text-xs text-gray-500">
                    Selecionados:
                    {{ form.tipo_arma_apreendida.join(', ') || 'Nenhum' }}
                  </p>
                  <p
                    v-if="erro('tipo_arma_apreendida')"
                    class="mt-1 text-sm text-red-600"
                  >
                    {{ erro('tipo_arma_apreendida') }}
                  </p>
                </div>

                <!-- Detalhes -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Detalhes - Tipo/Calibre <span class="text-red-500">*</span>
                  </label>
                  <textarea
                    v-model="form.detalhes_armas_apreendidas"
                    rows="3"
                    placeholder="Ex: 2 pistolas .40, 1 revólver .38"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#bea55a] focus:border-transparent"
                    :class="
                      erro('detalhes_armas_apreendidas')
                        ? 'border-red-500'
                        : 'border-gray-300'
                    "
                  ></textarea>
                  <p
                    v-if="erro('detalhes_armas_apreendidas')"
                    class="mt-1 text-sm text-red-600"
                  >
                    {{ erro('detalhes_armas_apreendidas') }}
                  </p>
                </div>

                <!-- Munições -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Munições / Calibre <span class="text-red-500">*</span>
                  </label>
                  <textarea
                    v-model="form.municoes_apreendidas"
                    rows="3"
                    placeholder="Ex: 150 munições .40 ou N/A"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#bea55a] focus:border-transparent"
                    :class="
                      erro('municoes_apreendidas')
                        ? 'border-red-500'
                        : 'border-gray-300'
                    "
                  ></textarea>
                  <p
                    v-if="erro('municoes_apreendidas')"
                    class="mt-1 text-sm text-red-600"
                  >
                    {{ erro('municoes_apreendidas') }}
                  </p>
                </div>
              </div>

              <!-- MENSAGEM quando NÃO -->
              <div
                v-else-if="houveApreensaoArmas === false"
                class="bg-gray-50 border border-gray-300 rounded-lg p-6 text-center"
              >
                <svg
                  class="w-16 h-16 mx-auto text-gray-400 mb-3"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                  ></path>
                </svg>
                <p class="text-lg font-medium text-gray-700">
                  Nenhuma arma foi apreendida
                </p>
              </div>
            </div>

            <!-- ETAPA 4: Entorpecentes -->
            <div v-show="etapaAtual === 4" class="space-y-6">
              <h2 class="text-xl font-semibold text-gray-800 border-b pb-3">
                Entorpecentes Apreendidos
              </h2>

              <!-- 23. Entorpecentes - CHECKBOXES -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">
                  Entorpecentes Apreendidos (Marque todos que se aplicam)
                  <span class="text-red-500">*</span>
                </label>

                <div
                  class="space-y-2 bg-gray-50 p-4 rounded-lg border border-gray-200"
                >
                  <div class="flex items-center">
                    <input
                      type="checkbox"
                      id="entorp_nenhum"
                      value="NENHUM"
                      v-model="form.entorpecente_apreendido"
                      :disabled="algumEntorpecenteMarcado"
                      class="w-4 h-4 text-[#bea55a] border-gray-300 rounded focus:ring-[#bea55a] disabled:opacity-50 disabled:cursor-not-allowed"
                    />
                    <label
                      for="entorp_nao"
                      class="ml-2 text-sm text-gray-700 cursor-pointer font-medium"
                      :class="algumEntorpecenteMarcado ? 'opacity-50' : ''"
                    >
                      Nenhum entorpecente apreendido
                    </label>
                  </div>

                  <div class="border-t border-gray-300 my-2 pt-2">
                    <p class="text-xs text-gray-500 mb-2">Tipos apreendidos:</p>
                  </div>

                  <div class="flex items-center">
                    <input
                      type="checkbox"
                      id="entorp_maconha"
                      value="MACONHA"
                      v-model="form.entorpecente_apreendido"
                      :disabled="nenhumEntorpecenteMarcado"
                      class="w-4 h-4 text-[#bea55a] border-gray-300 rounded focus:ring-[#bea55a] disabled:opacity-50 disabled:cursor-not-allowed"
                    />
                    <label
                      for="entorp_maconha"
                      class="ml-2 text-sm text-gray-700 cursor-pointer"
                      :class="nenhumEntorpecenteMarcado ? 'opacity-50' : ''"
                    >
                      Maconha
                    </label>
                  </div>

                  <div class="flex items-center">
                    <input
                      type="checkbox"
                      id="entorp_cocaina"
                      value="COCAINA"
                      v-model="form.entorpecente_apreendido"
                      :disabled="nenhumEntorpecenteMarcado"
                      class="w-4 h-4 text-[#bea55a] border-gray-300 rounded focus:ring-[#bea55a] disabled:opacity-50 disabled:cursor-not-allowed"
                    />
                    <label
                      for="entorp_cocaina"
                      class="ml-2 text-sm text-gray-700 cursor-pointer"
                      :class="nenhumEntorpecenteMarcado ? 'opacity-50' : ''"
                    >
                      Cocaína
                    </label>
                  </div>

                  <div class="flex items-center">
                    <input
                      type="checkbox"
                      id="entorp_crack"
                      value="CRACK"
                      v-model="form.entorpecente_apreendido"
                      :disabled="nenhumEntorpecenteMarcado"
                      class="w-4 h-4 text-[#bea55a] border-gray-300 rounded focus:ring-[#bea55a] disabled:opacity-50 disabled:cursor-not-allowed"
                    />
                    <label
                      for="entorp_crack"
                      class="ml-2 text-sm text-gray-700 cursor-pointer"
                      :class="nenhumEntorpecenteMarcado ? 'opacity-50' : ''"
                    >
                      Crack
                    </label>
                  </div>

                  <div class="flex items-center">
                    <input
                      type="checkbox"
                      id="entorp_skank"
                      value="SKANK"
                      v-model="form.entorpecente_apreendido"
                      :disabled="nenhumEntorpecenteMarcado"
                      class="w-4 h-4 text-[#bea55a] border-gray-300 rounded focus:ring-[#bea55a] disabled:opacity-50 disabled:cursor-not-allowed"
                    />
                    <label
                      for="entorp_skank"
                      class="ml-2 text-sm text-gray-700 cursor-pointer"
                      :class="nenhumEntorpecenteMarcado ? 'opacity-50' : ''"
                    >
                      Skank
                    </label>
                  </div>

                  <div class="flex items-center">
                    <input
                      type="checkbox"
                      id="entorp_heroina"
                      value="HEROINA"
                      v-model="form.entorpecente_apreendido"
                      :disabled="nenhumEntorpecenteMarcado"
                      class="w-4 h-4 text-[#bea55a] border-gray-300 rounded focus:ring-[#bea55a] disabled:opacity-50 disabled:cursor-not-allowed"
                    />
                    <label
                      for="entorp_heroina"
                      class="ml-2 text-sm text-gray-700 cursor-pointer"
                      :class="nenhumEntorpecenteMarcado ? 'opacity-50' : ''"
                    >
                      Heroína
                    </label>
                  </div>

                  <div class="flex items-center">
                    <input
                      type="checkbox"
                      id="entorp_lsd"
                      value="LSD"
                      v-model="form.entorpecente_apreendido"
                      :disabled="nenhumEntorpecenteMarcado"
                      class="w-4 h-4 text-[#bea55a] border-gray-300 rounded focus:ring-[#bea55a] disabled:opacity-50 disabled:cursor-not-allowed"
                    />
                    <label
                      for="entorp_lsd"
                      class="ml-2 text-sm text-gray-700 cursor-pointer"
                      :class="nenhumEntorpecenteMarcado ? 'opacity-50' : ''"
                    >
                      LSD
                    </label>
                  </div>

                  <div class="flex items-center">
                    <input
                      type="checkbox"
                      id="entorp_ecstasy"
                      value="ECSTASY (MDMA)"
                      v-model="form.entorpecente_apreendido"
                      :disabled="nenhumEntorpecenteMarcado"
                      class="w-4 h-4 text-[#bea55a] border-gray-300 rounded focus:ring-[#bea55a] disabled:opacity-50 disabled:cursor-not-allowed"
                    />
                    <label
                      for="entorp_ecstasy"
                      class="ml-2 text-sm text-gray-700 cursor-pointer"
                      :class="nenhumEntorpecenteMarcado ? 'opacity-50' : ''"
                    >
                      Ecstasy (MDMA)
                    </label>
                  </div>

                  <div class="flex items-center">
                    <input
                      type="checkbox"
                      id="entorp_outros"
                      value="OUTROS"
                      v-model="form.entorpecente_apreendido"
                      :disabled="nenhumEntorpecenteMarcado"
                      class="w-4 h-4 text-[#bea55a] border-gray-300 rounded focus:ring-[#bea55a] disabled:opacity-50 disabled:cursor-not-allowed"
                    />
                    <label
                      for="entorp_outros"
                      class="ml-2 text-sm text-gray-700 cursor-pointer"
                      :class="nenhumEntorpecenteMarcado ? 'opacity-50' : ''"
                    >
                      Outros
                    </label>
                  </div>
                </div>

                <p class="mt-2 text-xs text-gray-500">
                  Selecionados:
                  {{
                    form.entorpecente_apreendido.length > 0
                      ? form.entorpecente_apreendido.join(', ')
                      : 'Nenhum'
                  }}
                </p>

                <p
                  v-if="erro('entorpecente_apreendido')"
                  class="mt-1 text-sm text-red-600"
                >
                  {{ erro('entorpecente_apreendido') }}
                </p>
              </div>

              <!-- 24. Detalhes dos Entorpecentes -->
              <div
                v-if="
                  !form.entorpecente_apreendido.includes('NENHUM') &&
                  form.entorpecente_apreendido.length > 0
                "
              >
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Especificar Peso/Quantidade dos Entorpecentes
                  <span class="text-red-500">*</span>
                </label>
                <textarea
                  v-model="form.detalhes_entorpecentes"
                  rows="3"
                  placeholder="Ex: 500g de maconha, 200g de cocaína, 50 comprimidos de Ecstasy"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#bea55a] focus:border-transparent"
                  :class="
                    erro('detalhes_entorpecentes')
                      ? 'border-red-500'
                      : 'border-gray-300'
                  "
                ></textarea>
                <p class="mt-1 text-xs text-gray-500">
                  Especifique peso em gramas ou quantidade de unidades para cada
                  tipo.
                </p>
                <p
                  v-if="erro('detalhes_entorpecentes')"
                  class="mt-1 text-sm text-red-600"
                >
                  {{ erro('detalhes_entorpecentes') }}
                </p>
              </div>
            </div>

            <!-- ETAPA 5: Valores e Objetos -->
            <div v-show="etapaAtual === 5" class="space-y-6">
              <h2 class="text-xl font-semibold text-gray-800 border-b pb-3">
                Valores e Objetos Apreendidos
              </h2>

              <!-- 25. Valores em Dinheiro -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Valores em Dinheiro (R$) <span class="text-red-500">*</span>
                </label>
                <input
                  v-model.number="form.valores_dinheiro"
                  type="number"
                  min="0"
                  step="0.01"
                  @keypress="apenasNumeros"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#bea55a] focus:border-transparent"
                  :class="
                    erro('valores_dinheiro')
                      ? 'border-red-500'
                      : 'border-gray-300'
                  "
                />

                <!-- Exibição formatada -->
                <p class="mt-2 text-sm font-medium text-gray-700">
                  Valor formatado:
                  <span class="text-[#bea55a]">{{
                    form.valores_dinheiro_formatado
                  }}</span>
                </p>

                <p class="mt-1 text-xs text-gray-500">
                  Informe 0 se não houver valores apreendidos.
                </p>
                <p
                  v-if="erro('valores_dinheiro')"
                  class="mt-1 text-sm text-red-600"
                >
                  {{ erro('valores_dinheiro') }}
                </p>
              </div>

              <!-- 26. Veículos -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Veículos Apreendidos (Marca/Modelo/Placa/Ano/Cor/Valor FIPE)
                  <span class="text-red-500">*</span>
                </label>
                <textarea
                  v-model="form.veiculos_apreendidos"
                  rows="4"
                  placeholder="Ex: Fiat Uno - Placa ABC-1234 - 2020 - Branco - R$ 35.000,00&#10;Toyota Corolla - Placa XYZ-5678 - 2021 - Prata - R$ 95.000,00&#10;&#10;Se não houver, informar: N/A"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#bea55a] focus:border-transparent"
                  :class="
                    erro('veiculos_apreendidos')
                      ? 'border-red-500'
                      : 'border-gray-300'
                  "
                ></textarea>
                <p class="mt-1 text-xs text-gray-500">
                  Liste um veículo por linha com todos os dados. Se não houver,
                  informe "N/A".
                </p>
                <p
                  v-if="erro('veiculos_apreendidos')"
                  class="mt-1 text-sm text-red-600"
                >
                  {{ erro('veiculos_apreendidos') }}
                </p>
              </div>

              <!-- 27. Demais Objetos -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Demais Objetos Apreendidos (Atribuir Valor Estimado)
                  <span class="text-red-500">*</span>
                </label>
                <textarea
                  v-model="form.demais_objetos_apreendidos"
                  rows="3"
                  placeholder="Ex: 2 notebooks - R$ 5.000,00, 3 celulares - R$ 3.000,00, 1 TV 50 polegadas - R$ 2.000,00&#10;&#10;Se não houver, informar: N/A"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#bea55a] focus:border-transparent"
                  :class="
                    erro('demais_objetos_apreendidos')
                      ? 'border-red-500'
                      : 'border-gray-300'
                  "
                ></textarea>
                <p class="mt-1 text-xs text-gray-500">
                  Liste objetos com valores estimados. Se não houver, informe
                  "N/A".
                </p>
                <p
                  v-if="erro('demais_objetos_apreendidos')"
                  class="mt-1 text-sm text-red-600"
                >
                  {{ erro('demais_objetos_apreendidos') }}
                </p>
              </div>

              <!-- 28. Outras Informações (OPCIONAL) -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Outras Informações que Julgar Necessárias (Opcional)
                </label>
                <textarea
                  v-model="form.outras_informacoes"
                  rows="4"
                  placeholder="Informações adicionais relevantes sobre a operação..."
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#bea55a] focus:border-transparent"
                  :class="
                    erro('outras_informacoes')
                      ? 'border-red-500'
                      : 'border-gray-300'
                  "
                ></textarea>
                <p
                  v-if="erro('outras_informacoes')"
                  class="mt-1 text-sm text-red-600"
                >
                  {{ erro('outras_informacoes') }}
                </p>
              </div>
            </div>

            <!-- Botões de Navegação -->
            <div class="flex justify-between mt-8 pt-6 border-t">
              <button
                v-if="etapaAtual > 1"
                type="button"
                @click="voltarEtapa"
                class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
              >
                ← Voltar
              </button>
              <div v-else></div>

              <button
                v-if="etapaAtual < totalEtapas"
                type="button"
                @click="proximaEtapa"
                class="px-6 py-3 bg-[#bea55a] text-white rounded-lg hover:bg-[#968143] transition-colors"
              >
                Próximo →
              </button>

              <button
                v-else
                type="submit"
                :disabled="form.processing"
                class="px-6 py-3 bg-[#bea55a] text-white rounded-lg hover:bg-[#968143] transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
              >
                <svg
                  v-if="form.processing"
                  class="animate-spin h-5 w-5"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                  ></circle>
                  <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                  ></path>
                </svg>
                {{ form.processing ? 'Salvando...' : 'Salvar Resultado' }}
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <Footer />
  </div>
</template>

<style scoped>
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

input[type='number'] {
  -moz-appearance: textfield;
}
</style>
