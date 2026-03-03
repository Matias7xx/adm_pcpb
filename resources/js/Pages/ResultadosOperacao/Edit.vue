<script setup>
import { ref, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import Header from '../Components/Header.vue';
import SiteNavbar from '../Components/SiteNavbar.vue';
import Footer from '../Components/Footer.vue';

const props = defineProps({
  resultado: Object,
  opcoes: Object,
});

const etapaAtual = ref(1);
const totalEtapas = 5;

// Derivar houveApreensaoArmas a partir dos dados existentes
const houveApreensaoArmas = ref(
  !Array.isArray(props.resultado.tipo_arma_apreendida) ||
    !props.resultado.tipo_arma_apreendida.includes('NENHUMA')
);

const form = useForm({
  operacao_id: props.resultado.operacao_id,

  // Autoridade (editável)
  autoridade_responsavel_nome:
    props.resultado.autoridade_responsavel_nome || '',
  autoridade_responsavel_matricula: String(
    props.resultado.autoridade_responsavel_matricula || ''
  ),

  numero_processo_pje: props.resultado.numero_processo_pje || '',

  // Mandados
  mandados_prisao_cumpridos: props.resultado.mandados_prisao_cumpridos ?? 0,
  mandados_prisao_cumpridos_detalhes:
    props.resultado.mandados_prisao_cumpridos_detalhes || '',
  mandados_prisao_nao_cumpridos:
    props.resultado.mandados_prisao_nao_cumpridos ?? 0,
  mandados_busca_cumpridos: props.resultado.mandados_busca_cumpridos ?? 0,
  mandados_busca_infrator_cumpridos:
    props.resultado.mandados_busca_infrator_cumpridos ?? 0,
  mandados_busca_infrator_nao_cumpridos:
    props.resultado.mandados_busca_infrator_nao_cumpridos ?? 0,

  // Prisões
  prisoes_flagrante: props.resultado.prisoes_flagrante ?? 0,

  // Armas
  quantidade_armas_apreendidas:
    props.resultado.quantidade_armas_apreendidas ?? 0,
  tipo_arma_apreendida: Array.isArray(props.resultado.tipo_arma_apreendida)
    ? [...props.resultado.tipo_arma_apreendida]
    : [],
  detalhes_armas_apreendidas: props.resultado.detalhes_armas_apreendidas || '',

  // Munições
  municoes_apreendidas: props.resultado.municoes_apreendidas || '',

  // Entorpecentes
  entorpecente_apreendido: Array.isArray(
    props.resultado.entorpecente_apreendido
  )
    ? [...props.resultado.entorpecente_apreendido]
    : [],
  detalhes_entorpecentes: props.resultado.detalhes_entorpecentes || '',

  // Valores e objetos
  valores_dinheiro: props.resultado.valores_dinheiro ?? 0,
  valores_dinheiro_formatado: '',
  veiculos_apreendidos: props.resultado.veiculos_apreendidos || '',
  demais_objetos_apreendidos: props.resultado.demais_objetos_apreendidos || '',
  outras_informacoes: props.resultado.outras_informacoes || '',
});

// Inicializar formatação do dinheiro
form.valores_dinheiro_formatado = formatarDinheiro(form.valores_dinheiro);

const apenasNumeros = event => {
  if (!/^[0-9]$/.test(String.fromCharCode(event.keyCode))) {
    event.preventDefault();
  }
};

function formatarDinheiro(valor) {
  if (valor === null || valor === undefined || valor === '') return 'R$ 0,00';
  const numero = typeof valor === 'string' ? parseFloat(valor) : valor;
  if (isNaN(numero)) return 'R$ 0,00';
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  }).format(numero);
}

const formatarData = data => {
  if (!data) return '';
  const soData = String(data).substring(0, 10);
  return new Date(soData + 'T12:00:00').toLocaleDateString('pt-BR');
};

watch(
  () => form.valores_dinheiro,
  novoValor => {
    form.valores_dinheiro_formatado = formatarDinheiro(novoValor);
  }
);

watch(houveApreensaoArmas, novoValor => {
  if (!novoValor) {
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

watch(
  () => form.tipo_arma_apreendida,
  novoValor => {
    if (novoValor.includes('NENHUMA') && novoValor.length > 1) {
      form.tipo_arma_apreendida = ['NENHUMA'];
    }
    if (novoValor.includes('NENHUMA')) {
      form.quantidade_armas_apreendidas = 0;
      form.detalhes_armas_apreendidas = 'N/A';
      form.municoes_apreendidas = 'N/A';
    }
  }
);

watch(
  () => form.entorpecente_apreendido,
  novoValor => {
    if (novoValor.includes('NENHUM') && novoValor.length > 1) {
      form.entorpecente_apreendido = ['NENHUM'];
    }
    if (novoValor.includes('NENHUM')) {
      form.detalhes_entorpecentes = '';
    }
  }
);

// Computed helpers
const nenhumaArmaMarcada = () => form.tipo_arma_apreendida.includes('NENHUMA');
const algumaArmaMarcada = () =>
  form.tipo_arma_apreendida.some(t => t !== 'NENHUMA');
const nenhumEntorpecenteMarcado = () =>
  form.entorpecente_apreendido.includes('NENHUM');
const algumEntorpecenteMarcado = () =>
  form.entorpecente_apreendido.some(t => t !== 'NENHUM');

const errorsEtapa = ref({});

const campoNumericoInvalido = valor =>
  valor === null ||
  valor === undefined ||
  valor === '' ||
  isNaN(valor) ||
  valor < 0;

const validarEtapa = etapa => {
  const erros = {};

  if (etapa === 1) {
    if (!form.autoridade_responsavel_nome?.trim())
      erros.autoridade_responsavel_nome = 'Nome da autoridade é obrigatório.';
    if (!String(form.autoridade_responsavel_matricula || '').trim())
      erros.autoridade_responsavel_matricula =
        'Matrícula da autoridade é obrigatória.';
  }

  if (etapa === 2) {
    if (campoNumericoInvalido(form.mandados_prisao_cumpridos))
      erros.mandados_prisao_cumpridos =
        'Este campo é obrigatório (use 0 se não houver).';
    if (!form.mandados_prisao_cumpridos_detalhes?.trim())
      erros.mandados_prisao_cumpridos_detalhes = 'Informe os detalhes ou N/A.';
    if (campoNumericoInvalido(form.mandados_prisao_nao_cumpridos))
      erros.mandados_prisao_nao_cumpridos =
        'Este campo é obrigatório (use 0 se não houver).';
    if (campoNumericoInvalido(form.mandados_busca_cumpridos))
      erros.mandados_busca_cumpridos =
        'Este campo é obrigatório (use 0 se não houver).';
    if (campoNumericoInvalido(form.mandados_busca_infrator_cumpridos))
      erros.mandados_busca_infrator_cumpridos =
        'Este campo é obrigatório (use 0 se não houver).';
    if (campoNumericoInvalido(form.mandados_busca_infrator_nao_cumpridos))
      erros.mandados_busca_infrator_nao_cumpridos =
        'Este campo é obrigatório (use 0 se não houver).';
    if (campoNumericoInvalido(form.prisoes_flagrante))
      erros.prisoes_flagrante =
        'Este campo é obrigatório (use 0 se não houver).';
  }

  if (etapa === 3) {
    if (!houveApreensaoArmas.value) {
      if (!form.tipo_arma_apreendida.includes('NENHUMA'))
        form.tipo_arma_apreendida = ['NENHUMA'];
    } else {
      if (campoNumericoInvalido(form.quantidade_armas_apreendidas))
        erros.quantidade_armas_apreendidas = 'Este campo é obrigatório.';
      if (form.tipo_arma_apreendida.length === 0)
        erros.tipo_arma_apreendida = 'Selecione pelo menos um tipo.';
      if (!form.detalhes_armas_apreendidas?.trim())
        erros.detalhes_armas_apreendidas = 'Informe os detalhes.';
      if (!form.municoes_apreendidas?.trim())
        erros.municoes_apreendidas = 'Informe as munições ou N/A.';
    }
  }

  if (etapa === 4) {
    if (form.entorpecente_apreendido.length === 0)
      erros.entorpecente_apreendido = 'Selecione pelo menos uma opção.';
    if (
      !form.entorpecente_apreendido.includes('NENHUM') &&
      form.entorpecente_apreendido.length > 0 &&
      !form.detalhes_entorpecentes?.trim()
    )
      erros.detalhes_entorpecentes = 'Informe os detalhes dos entorpecentes.';
  }

  if (etapa === 5) {
    if (campoNumericoInvalido(form.valores_dinheiro))
      erros.valores_dinheiro =
        'Este campo é obrigatório (use 0 se não houver).';
    if (!form.veiculos_apreendidos?.trim())
      erros.veiculos_apreendidos = 'Informe os veículos ou N/A.';
    if (!form.demais_objetos_apreendidos?.trim())
      erros.demais_objetos_apreendidos = 'Informe os objetos ou N/A.';
  }

  return erros;
};

const proximaEtapa = () => {
  const erros = validarEtapa(etapaAtual.value);
  errorsEtapa.value = erros;
  if (Object.keys(erros).length > 0) return;
  if (etapaAtual.value < totalEtapas) etapaAtual.value++;
};

const voltarEtapa = () => {
  errorsEtapa.value = {};
  if (etapaAtual.value > 1) etapaAtual.value--;
};

const submit = () => {
  const erros = validarEtapa(etapaAtual.value);
  errorsEtapa.value = erros;
  if (Object.keys(erros).length > 0) return;

  form.autoridade_responsavel_matricula = String(
    form.autoridade_responsavel_matricula || ''
  );

  if (
    !houveApreensaoArmas.value ||
    form.tipo_arma_apreendida.includes('NENHUMA')
  ) {
    form.tipo_arma_apreendida = ['NENHUMA'];
    form.quantidade_armas_apreendidas = 0;
    form.detalhes_armas_apreendidas = 'N/A';
    form.municoes_apreendidas = 'N/A';
  }

  // valores_dinheiro_formatado é só máscara
  const { valores_dinheiro_formatado, ...dadosParaEnviar } = form.data();

  form
    .transform(() => dadosParaEnviar)
    .put(route('resultados-operacao.update', props.resultado.id), {
      preserveScroll: true,
      onError: () => {
        // Se houver erros do servidor, volta para a etapa 1 para o usuário ver
        etapaAtual.value = 1;
      },
    });
};

const erro = campo => form.errors[campo] || errorsEtapa.value[campo];
</script>

<template>
  <div>
    <Head>
      <title>Editar Resultado — {{ resultado.operacao.nome_operacao }}</title>
    </Head>

    <Header />
    <SiteNavbar />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
      <Link
        :href="route('resultados-operacao.show', resultado.id)"
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
        Voltar para o Resultado
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
                Editar Resultado da Operação
              </h1>
              <p class="text-gray-600 mt-1">
                {{ resultado.operacao.nome_operacao }}
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

              <!-- Dados da Operação (Readonly) -->
              <div class="bg-gray-50 border-2 border-gray-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                  Dados da Operação Cadastrada
                </h3>
                <div
                  class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4"
                >
                  <div class="bg-white p-3 rounded border border-gray-200">
                    <label class="text-xs font-semibold text-gray-700 uppercase"
                      >Nome da Operação</label
                    >
                    <p class="text-gray-900 font-medium mt-1">
                      {{ resultado.operacao.nome_operacao }}
                    </p>
                  </div>

                  <div class="bg-white p-3 rounded border border-gray-200">
                    <label class="text-xs font-semibold text-gray-700 uppercase"
                      >Data da Deflagração</label
                    >
                    <p class="text-gray-900 font-medium mt-1">
                      {{ formatarData(resultado.operacao.data_operacao) }}
                    </p>
                  </div>

                  <div class="bg-white p-3 rounded border border-gray-200">
                    <label class="text-xs font-semibold text-gray-700 uppercase"
                      >Origem</label
                    >
                    <p class="text-gray-900 font-medium mt-1">
                      {{ resultado.operacao.origem_operacao }}
                    </p>
                  </div>

                  <div class="bg-white p-3 rounded border border-gray-200">
                    <label class="text-xs font-semibold text-gray-700 uppercase"
                      >UF Responsável</label
                    >
                    <p class="text-gray-900 font-medium mt-1">
                      {{ resultado.operacao.uf_responsavel }}
                    </p>
                  </div>

                  <div class="bg-white p-3 rounded border border-gray-200">
                    <label class="text-xs font-semibold text-gray-700 uppercase"
                      >Vinculada à</label
                    >
                    <p class="text-gray-900 font-medium mt-1">
                      {{ resultado.operacao.vinculada_unidade }}
                    </p>
                  </div>

                  <div class="bg-white p-3 rounded border border-gray-200">
                    <label class="text-xs font-semibold text-gray-700 uppercase"
                      >Unidade Executora</label
                    >
                    <p class="text-gray-900 font-medium mt-1">
                      {{ resultado.operacao.vinculada_unidade_especializada }}
                    </p>
                  </div>

                  <div
                    class="bg-white p-3 rounded border border-gray-200 md:col-span-2"
                  >
                    <label class="text-xs font-semibold text-gray-700 uppercase"
                      >Cidades Alvo</label
                    >
                    <p class="text-gray-900 mt-1 text-sm">
                      {{ resultado.operacao.cidades_alvo }}
                    </p>
                  </div>

                  <div
                    class="bg-white p-3 rounded border border-gray-200 md:col-span-2"
                  >
                    <label class="text-xs font-semibold text-gray-700 uppercase"
                      >Crimes Investigados</label
                    >
                    <p class="text-gray-900 mt-1 text-sm">
                      {{ resultado.operacao.crimes_investigados }}
                    </p>
                  </div>

                  <!-- UFs do Alvo -->
                  <div
                    v-if="
                      resultado.operacao.origem_operacao ===
                        'Alvo em outro Estado' &&
                      resultado.operacao.ufs_alvo_outros_estados &&
                      Object.keys(resultado.operacao.ufs_alvo_outros_estados)
                        .length
                    "
                    class="bg-blue-50 p-3 rounded border border-blue-200 md:col-span-3"
                  >
                    <div class="flex items-center justify-between mb-2">
                      <label
                        class="text-xs font-semibold text-blue-700 uppercase"
                      >
                        Estado(s) e Quantidade de Alvos por Estado
                      </label>
                      <span class="text-xs text-blue-600">
                        Total fora da PB:
                        <strong>{{
                          resultado.operacao.quantidade_alvos_outros_estados
                        }}</strong>
                      </span>
                    </div>
                    <div class="flex flex-wrap gap-2">
                      <span
                        v-for="(qtd, uf) in resultado.operacao
                          .ufs_alvo_outros_estados"
                        :key="uf"
                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-300"
                      >
                        {{ uf }}
                        <span
                          class="bg-blue-200 text-blue-900 px-1.5 py-0.5 rounded-full text-xs"
                          >{{ qtd }} {{ qtd === 1 ? 'alvo' : 'alvos' }}</span
                        >
                      </span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Número PJE -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Número do Processo PJE
                  <span class="text-gray-500 text-xs"
                    >(Opcional — "PREJUDICADO" se nacional)</span
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

              <!-- Autoridade -->
              <div class="bg-gray-50 border-2 border-gray-300 rounded-lg p-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">
                  Autoridade Policial Responsável pelo Resultado
                </h3>
                <p class="text-sm text-gray-600 mb-4">
                  Por padrão, usa a mesma autoridade informada no cadastro da
                  operação. Edite se for diferente.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2"
                      >Nome da Autoridade
                      <span class="text-red-500">*</span></label
                    >
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
                    <label class="block text-sm font-medium text-gray-700 mb-2"
                      >Matrícula da Autoridade
                      <span class="text-red-500">*</span></label
                    >
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

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2"
                  >Mandados de Prisão Cumpridos
                  <span class="text-red-500">*</span></label
                >
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

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2"
                  >Detalhes dos Presos (Nome e CPF)
                  <span class="text-red-500">*</span></label
                >
                <textarea
                  v-model="form.mandados_prisao_cumpridos_detalhes"
                  rows="4"
                  placeholder="João da Silva - CPF 123.456.789-00&#10;Se não houver, informar: N/A"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#bea55a] focus:border-transparent"
                  :class="
                    erro('mandados_prisao_cumpridos_detalhes')
                      ? 'border-red-500'
                      : 'border-gray-300'
                  "
                ></textarea>
                <p
                  v-if="erro('mandados_prisao_cumpridos_detalhes')"
                  class="mt-1 text-sm text-red-600"
                >
                  {{ erro('mandados_prisao_cumpridos_detalhes') }}
                </p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2"
                  >Mandados de Prisão Não Cumpridos
                  <span class="text-red-500">*</span></label
                >
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
                />
                <p
                  v-if="erro('mandados_prisao_nao_cumpridos')"
                  class="mt-1 text-sm text-red-600"
                >
                  {{ erro('mandados_prisao_nao_cumpridos') }}
                </p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2"
                  >Mandados de Busca e Apreensão Cumpridos
                  <span class="text-red-500">*</span></label
                >
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
                <label class="block text-sm font-medium text-gray-700 mb-2"
                  >Mandados de Busca de Infrator Cumpridos
                  <span class="text-red-500">*</span></label
                >
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
                <label class="block text-sm font-medium text-gray-700 mb-2"
                  >Mandados de Busca de Infrator Não Cumpridos
                  <span class="text-red-500">*</span></label
                >
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

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2"
                  >Prisões em Flagrante
                  <span class="text-red-500">*</span></label
                >
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
                <p class="mt-1 text-xs text-gray-500">
                  Informe 0 se não houver prisões em flagrante.
                </p>
                <p
                  v-if="erro('prisoes_flagrante')"
                  class="mt-1 text-sm text-red-600"
                >
                  {{ erro('prisoes_flagrante') }}
                </p>
              </div>
            </div>

            <!-- ETAPA 3: Armas -->
            <div v-show="etapaAtual === 3" class="space-y-6">
              <h2 class="text-xl font-semibold text-gray-800 border-b pb-3">
                Armas e Munições Apreendidas
              </h2>

              <!-- Toggle houve apreensão -->
              <div class="bg-gray-50 border-2 border-gray-200 rounded-lg p-4">
                <h3 class="text-base font-semibold text-gray-900 mb-3">
                  Houve apreensão de arma(s)?
                </h3>
                <div class="flex gap-4">
                  <label class="flex items-center gap-2 cursor-pointer">
                    <input
                      type="radio"
                      :value="true"
                      v-model="houveApreensaoArmas"
                      class="text-[#bea55a]"
                    />
                    <span class="text-sm font-medium text-gray-700">Sim</span>
                  </label>
                  <label class="flex items-center gap-2 cursor-pointer">
                    <input
                      type="radio"
                      :value="false"
                      v-model="houveApreensaoArmas"
                      class="text-[#bea55a]"
                    />
                    <span class="text-sm font-medium text-gray-700">Não</span>
                  </label>
                </div>
              </div>

              <template v-if="houveApreensaoArmas">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2"
                    >Quantidade de Armas
                    <span class="text-red-500">*</span></label
                  >
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

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-3"
                    >Tipo(s) de Arma(s) Apreendida(s)
                    <span class="text-red-500">*</span></label
                  >
                  <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <label
                      v-for="(label, valor) in opcoes.tipos_arma"
                      :key="valor"
                      class="flex items-center gap-2 p-3 border rounded-lg cursor-pointer hover:bg-gray-50"
                      :class="
                        form.tipo_arma_apreendida.includes(valor)
                          ? 'border-[#bea55a] bg-amber-50'
                          : 'border-gray-200'
                      "
                    >
                      <input
                        type="checkbox"
                        :value="valor"
                        v-model="form.tipo_arma_apreendida"
                        :disabled="valor !== 'NENHUMA' && nenhumaArmaMarcada()"
                        class="rounded text-[#bea55a]"
                      />
                      <span class="text-sm">{{ label }}</span>
                    </label>
                  </div>
                  <p
                    v-if="erro('tipo_arma_apreendida')"
                    class="mt-1 text-sm text-red-600"
                  >
                    {{ erro('tipo_arma_apreendida') }}
                  </p>
                </div>

                <div v-if="algumaArmaMarcada()">
                  <label class="block text-sm font-medium text-gray-700 mb-2"
                    >Detalhes das Armas
                    <span class="text-red-500">*</span></label
                  >
                  <textarea
                    v-model="form.detalhes_armas_apreendidas"
                    rows="3"
                    placeholder="Ex: 2 revólveres calibre 38, 1 pistola 9mm sem numeração"
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

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2"
                    >Munições Apreendidas
                    <span class="text-red-500">*</span></label
                  >
                  <textarea
                    v-model="form.municoes_apreendidas"
                    rows="2"
                    placeholder="Ex: 50 munições calibre 38, 30 munições 9mm&#10;Se não houver, informar: N/A"
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
              </template>

              <div
                v-else
                class="bg-gray-100 p-4 rounded-lg border border-gray-300 text-center text-gray-500"
              >
                Nenhuma arma apreendida
              </div>
            </div>

            <!-- ETAPA 4: Entorpecentes -->
            <div v-show="etapaAtual === 4" class="space-y-6">
              <h2 class="text-xl font-semibold text-gray-800 border-b pb-3">
                Entorpecente(s) Apreendido(s)
              </h2>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-3"
                  >Tipo(s) de Entorpecente(s)
                  <span class="text-red-500">*</span></label
                >
                <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                  <label
                    v-for="(label, valor) in opcoes.tipos_entorpecente"
                    :key="valor"
                    class="flex items-center gap-2 p-3 border rounded-lg cursor-pointer hover:bg-gray-50"
                    :class="
                      form.entorpecente_apreendido.includes(valor)
                        ? 'border-[#bea55a] bg-amber-50'
                        : 'border-gray-200'
                    "
                  >
                    <input
                      type="checkbox"
                      :value="valor"
                      v-model="form.entorpecente_apreendido"
                      :disabled="
                        valor !== 'NENHUM' && nenhumEntorpecenteMarcado()
                      "
                      class="rounded text-[#bea55a]"
                    />
                    <span class="text-sm">{{ label }}</span>
                  </label>
                </div>
                <p
                  v-if="erro('entorpecente_apreendido')"
                  class="mt-1 text-sm text-red-600"
                >
                  {{ erro('entorpecente_apreendido') }}
                </p>
              </div>

              <div v-if="algumEntorpecenteMarcado()">
                <label class="block text-sm font-medium text-gray-700 mb-2"
                  >Especificar Peso/Quantidade
                  <span class="text-red-500">*</span></label
                >
                <textarea
                  v-model="form.detalhes_entorpecentes"
                  rows="3"
                  placeholder="Ex: 500g de maconha, 200g de cocaína"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#bea55a] focus:border-transparent"
                  :class="
                    erro('detalhes_entorpecentes')
                      ? 'border-red-500'
                      : 'border-gray-300'
                  "
                ></textarea>
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

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2"
                  >Valores em Dinheiro (R$)
                  <span class="text-red-500">*</span></label
                >
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
                <p class="mt-2 text-sm font-medium text-gray-700">
                  Valor formatado:
                  <span class="text-[#bea55a]">{{
                    form.valores_dinheiro_formatado
                  }}</span>
                </p>
                <p
                  v-if="erro('valores_dinheiro')"
                  class="mt-1 text-sm text-red-600"
                >
                  {{ erro('valores_dinheiro') }}
                </p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2"
                  >Veículos Apreendidos (Marca/Modelo/Placa/Ano/Cor/Valor FIPE)
                  <span class="text-red-500">*</span></label
                >
                <textarea
                  v-model="form.veiculos_apreendidos"
                  rows="4"
                  placeholder="Ex: Fiat Uno - Placa ABC-1234 - 2020 - Branco - R$ 35.000,00&#10;Se não houver, informar: N/A"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#bea55a] focus:border-transparent"
                  :class="
                    erro('veiculos_apreendidos')
                      ? 'border-red-500'
                      : 'border-gray-300'
                  "
                ></textarea>
                <p
                  v-if="erro('veiculos_apreendidos')"
                  class="mt-1 text-sm text-red-600"
                >
                  {{ erro('veiculos_apreendidos') }}
                </p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2"
                  >Demais Objetos Apreendidos (Atribuir Valor Estimado)
                  <span class="text-red-500">*</span></label
                >
                <textarea
                  v-model="form.demais_objetos_apreendidos"
                  rows="3"
                  placeholder="Ex: 2 notebooks - R$ 5.000,00&#10;Se não houver, informar: N/A"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#bea55a] focus:border-transparent"
                  :class="
                    erro('demais_objetos_apreendidos')
                      ? 'border-red-500'
                      : 'border-gray-300'
                  "
                ></textarea>
                <p
                  v-if="erro('demais_objetos_apreendidos')"
                  class="mt-1 text-sm text-red-600"
                >
                  {{ erro('demais_objetos_apreendidos') }}
                </p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2"
                  >Outras Informações (Opcional)</label
                >
                <textarea
                  v-model="form.outras_informacoes"
                  rows="4"
                  placeholder="Informações adicionais relevantes sobre a operação..."
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-[#bea55a] focus:border-transparent border-gray-300"
                ></textarea>
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

              <div class="flex items-center gap-3">
              <button
                v-if="etapaAtual < totalEtapas"
                type="button"
                @click="proximaEtapa"
                class="px-6 py-3 bg-[#bea55a] text-white rounded-lg hover:bg-[#968143] transition-colors"
              >
                Próximo →
              </button>

              <button
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
                {{ form.processing ? 'Salvando...' : 'Salvar Alterações' }}
              </button>
              </div>
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
