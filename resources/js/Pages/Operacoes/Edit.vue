<script setup>
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Head, Link } from '@inertiajs/vue3';
import Header from '../Components/Header.vue';
import SiteNavbar from '../Components/SiteNavbar.vue';
import Footer from '../Components/Footer.vue';

const props = defineProps({
  operacao: Object,
  opcoes: Object,
});

const isEdit = true;
const isAlvoEmOutroEstado = computed(
  () => form.origem_operacao === 'Alvo em outro Estado'
);

// UF alvo com quantidade
const ufAlvoSelecionada = ref('');
const ufAlvoQuantidade = ref(1);

const ufsDisponiveis = computed(() =>
  Object.keys(props.opcoes?.ufs || {}).filter(
    uf =>
      uf !== form.uf_responsavel &&
      !Object.keys(form.ufs_alvo_outros_estados).includes(uf)
  )
);

const adicionarUfAlvo = () => {
  if (!ufAlvoSelecionada.value || ufAlvoQuantidade.value < 1) return;
  form.ufs_alvo_outros_estados = {
    ...form.ufs_alvo_outros_estados,
    [ufAlvoSelecionada.value]: ufAlvoQuantidade.value,
  };
  form.quantidade_alvos_outros_estados = Object.values(
    form.ufs_alvo_outros_estados
  ).reduce((acc, v) => acc + Number(v), 0);
  ufAlvoSelecionada.value = '';
  ufAlvoQuantidade.value = 1;
};

const removerUfAlvo = uf => {
  const novo = { ...form.ufs_alvo_outros_estados };
  delete novo[uf];
  form.ufs_alvo_outros_estados = novo;
  form.quantidade_alvos_outros_estados = Object.values(novo).reduce(
    (acc, v) => acc + Number(v),
    0
  );
};

const form = useForm({
  nome_operacao: props.operacao.nome_operacao,
  autoridade_responsavel_nome: props.operacao.autoridade_responsavel_nome,
  autoridade_responsavel_matricula:
    props.operacao.autoridade_responsavel_matricula,
  origem_operacao: props.operacao.origem_operacao,
  uf_responsavel: props.operacao.uf_responsavel,
  ufs_alvo_outros_estados: props.operacao?.ufs_alvo_outros_estados || {},
  data_operacao: props.operacao.data_operacao?.substring(0, 10) || '',
  local_briefing: props.operacao.local_briefing,
  horario_briefing: props.operacao.horario_briefing?.substring(0, 5) || '',
  quantidade_total_alvos: props.operacao.quantidade_total_alvos,
  quantidade_mandados_prisao: props.operacao.quantidade_mandados_prisao,
  quantidade_mandados_busca_apreensao:
    props.operacao.quantidade_mandados_busca_apreensao,
  quantidade_mandados_busca_apreensao_infrator:
    props.operacao.quantidade_mandados_busca_apreensao_infrator,
  quantidade_alvos_outros_estados:
    props.operacao.quantidade_alvos_outros_estados,
  quantidade_policiais_empregados:
    props.operacao.quantidade_policiais_empregados,
  quantidade_viaturas_empregadas: props.operacao.quantidade_viaturas_empregadas,
  cidades_alvo: props.operacao.cidades_alvo,
  crimes_investigados: props.operacao.crimes_investigados,
  vinculada_unidade: props.operacao.vinculada_unidade,
  vinculada_unidade_especializada:
    props.operacao.vinculada_unidade_especializada,
  outra_unidade_policial: props.operacao.outra_unidade_policial || '',
  vinculada_delegacia_seccional: props.operacao.vinculada_delegacia_seccional,
  solicitacao_apoio_diop: props.operacao.solicitacao_apoio_diop || '',
  justificativa_edicao: '',
});

// Controle de etapas
const etapaAtual = ref(1);
const totalEtapas = 5;

// Erros de validação client-side por etapa
const errorsEtapa = ref({});

const modalJustificativa = ref(false);
const justificativaTexto = ref('');
const justificativaErro = ref('');

// Validação por etapa
const validarEtapa = etapa => {
  const erros = {};

  if (etapa === 1) {
    if (!form.nome_operacao?.trim())
      erros.nome_operacao = 'O nome da operação é obrigatório.';
    if (!form.autoridade_responsavel_nome?.trim())
      erros.autoridade_responsavel_nome = 'O nome da autoridade é obrigatório.';
    if (!form.autoridade_responsavel_matricula?.trim())
      erros.autoridade_responsavel_matricula =
        'A matrícula da autoridade é obrigatória.';
    if (!form.origem_operacao)
      erros.origem_operacao = 'A origem da operação é obrigatória.';
    if (!form.uf_responsavel)
      erros.uf_responsavel = 'A UF responsável é obrigatória.';
    if (!form.data_operacao) {
      erros.data_operacao = 'A data da operação é obrigatória.';
    } else {
      const anoData = new Date(form.data_operacao).getUTCFullYear();
      const anoAtual = new Date().getFullYear();
      if (anoData !== anoAtual) {
        erros.data_operacao = `A data da operação deve ser do ano atual (${anoAtual}).`;
      }
    }
    if (!form.cidades_alvo?.trim())
      erros.cidades_alvo = 'As cidades alvo são obrigatórias.';
    if (!form.crimes_investigados?.trim())
      erros.crimes_investigados = 'Os crimes investigados são obrigatórios.';
    if (
      form.origem_operacao === 'Alvo em outro Estado' &&
      Object.keys(form.ufs_alvo_outros_estados).length === 0
    )
      erros.ufs_alvo_outros_estados =
        'Adicione ao menos um estado com a quantidade de alvos.';
  }

  if (etapa === 2) {
    if (!form.local_briefing?.trim())
      erros.local_briefing = 'O local do briefing é obrigatório.';
    if (!form.horario_briefing)
      erros.horario_briefing = 'O horário do briefing é obrigatório.';
  }

  if (etapa === 3) {
    if (
      form.quantidade_total_alvos === null ||
      form.quantidade_total_alvos === ''
    )
      erros.quantidade_total_alvos =
        'A quantidade total de alvos é obrigatória.';
    if (
      form.quantidade_mandados_prisao === null ||
      form.quantidade_mandados_prisao === ''
    )
      erros.quantidade_mandados_prisao =
        'A quantidade de mandados de prisão é obrigatória.';
    if (
      form.quantidade_mandados_busca_apreensao === null ||
      form.quantidade_mandados_busca_apreensao === ''
    )
      erros.quantidade_mandados_busca_apreensao =
        'A quantidade de mandados de busca é obrigatória.';
    if (
      form.quantidade_mandados_busca_apreensao_infrator === null ||
      form.quantidade_mandados_busca_apreensao_infrator === ''
    )
      erros.quantidade_mandados_busca_apreensao_infrator =
        'A quantidade de mandados de busca (infrator) é obrigatória.';
    if (
      form.quantidade_alvos_outros_estados === null ||
      form.quantidade_alvos_outros_estados === ''
    )
      erros.quantidade_alvos_outros_estados =
        'A quantidade de alvos em outros estados é obrigatória.';
    if (
      form.quantidade_policiais_empregados === null ||
      form.quantidade_policiais_empregados === '' ||
      form.quantidade_policiais_empregados < 1
    )
      erros.quantidade_policiais_empregados =
        'Deve haver pelo menos 1 policial empregado.';
    if (
      form.quantidade_viaturas_empregadas === null ||
      form.quantidade_viaturas_empregadas === ''
    )
      erros.quantidade_viaturas_empregadas =
        'A quantidade de viaturas é obrigatória.';
  }

  if (etapa === 4) {
    if (!form.vinculada_unidade)
      erros.vinculada_unidade = 'A unidade vinculada é obrigatória.';
    if (!form.vinculada_unidade_especializada)
      erros.vinculada_unidade_especializada =
        'A unidade especializada é obrigatória.';
    if (
      form.vinculada_unidade_especializada === 'OUTRA' &&
      !form.outra_unidade_policial?.trim()
    )
      erros.outra_unidade_policial =
        'Especifique a unidade policial quando selecionar "OUTRA".';
    if (!form.vinculada_delegacia_seccional)
      erros.vinculada_delegacia_seccional =
        'A delegacia seccional é obrigatória.';
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

const abrirModalJustificativa = () => {
  const erros = validarEtapa(etapaAtual.value);
  errorsEtapa.value = erros;
  if (Object.keys(erros).length > 0) return;
  justificativaTexto.value = '';
  justificativaErro.value = '';
  modalJustificativa.value = true;
};

const campoParaEtapa = {
  nome_operacao: 1,
  autoridade_responsavel_nome: 1,
  autoridade_responsavel_matricula: 1,
  origem_operacao: 1,
  uf_responsavel: 1,
  ufs_alvo_outros_estados: 1,
  data_operacao: 1,
  cidades_alvo: 1,
  crimes_investigados: 1,
  local_briefing: 2,
  horario_briefing: 2,
  quantidade_total_alvos: 3,
  quantidade_mandados_prisao: 3,
  quantidade_mandados_busca_apreensao: 3,
  quantidade_mandados_busca_apreensao_infrator: 3,
  quantidade_alvos_outros_estados: 3,
  quantidade_policiais_empregados: 3,
  quantidade_viaturas_empregadas: 3,
  vinculada_unidade: 4,
  vinculada_unidade_especializada: 4,
  outra_unidade_policial: 4,
  vinculada_delegacia_seccional: 4,
  solicitacao_apoio_diop: 5,
  justificativa_edicao: 5,
};

const confirmarEdicao = () => {
  if (justificativaTexto.value.trim().length < 10) {
    justificativaErro.value =
      'A justificativa deve ter ao menos 10 caracteres.';
    return;
  }
  modalJustificativa.value = false;
  form.justificativa_edicao = justificativaTexto.value.trim();
  form.put(route('operacoes.update', props.operacao.id), {
    preserveScroll: true,
    onError: errors => {
      const etapasComErro = Object.keys(errors)
        .map(campo => campoParaEtapa[campo])
        .filter(Boolean);
      if (etapasComErro.length > 0) {
        etapaAtual.value = Math.min(...etapasComErro);
      }
    },
  });
};

// Limpa o erro do servidor quando o campo é corrigido
watch(
  () => ({ ...form.data() }),
  (newVal, oldVal) => {
    const alterados = Object.keys(newVal).filter(
      k => JSON.stringify(newVal[k]) !== JSON.stringify(oldVal[k])
    );
    if (alterados.length > 0) {
      form.clearErrors(...alterados);
    }
  }
);

// Mostrar campo "outra unidade" apenas se necessário
const mostrarOutraUnidade = computed(() => {
  return form.vinculada_unidade_especializada === 'OUTRA';
});

// Helper: exibe erro do servidor OU da validação client-side
const erro = campo => {
  return form.errors[campo] || errorsEtapa.value[campo];
};
</script>

<template>
  <Head>
    <title>Editar Operação - {{ operacao.nome_operacao }}</title>
  </Head>

  <Header />
  <SiteNavbar />

  <div class="min-h-screen bg-gray-50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-5xl mx-auto">
      <!-- Cabeçalho -->
      <div class="bg-white shadow rounded-lg mb-6 p-6">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-4">
            <Link
              :href="route('operacoes.show', operacao.id)"
              class="text-gray-600 hover:text-gray-900"
            >
              <svg
                class="w-6 h-6"
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
            </Link>
            <div>
              <h1 class="text-3xl font-bold text-gray-900">Editar Operação</h1>
              <p class="mt-2 text-sm text-gray-600">
                Atualize as informações da operação {{ operacao.nome_operacao }}
              </p>
            </div>
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
                    ? 'Briefing'
                    : i === 3
                      ? 'Mandados'
                      : i === 4
                        ? 'Vinculações'
                        : 'Apoio'
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

      <!-- Banner de erros do servidor -->
      <div
        v-if="Object.keys(form.errors).length > 0"
        class="mb-4 rounded-lg border border-red-200 bg-red-50 p-4"
      >
        <p class="text-sm font-semibold text-red-700 mb-2">
          Corrija os erros abaixo antes de continuar:
        </p>
        <ul class="list-disc list-inside space-y-1">
          <li
            v-for="(msg, campo) in form.errors"
            :key="campo"
            class="text-sm text-red-600"
          >
            {{ msg }}
          </li>
        </ul>
      </div>

      <!-- Formulário -->
      <form @submit.prevent="submit" novalidate>
        <div class="bg-white shadow rounded-lg p-6">
          <!-- ETAPA 1: Identificação da Operação -->
          <div v-show="etapaAtual === 1" class="space-y-6">
            <h2 class="text-xl font-semibold text-gray-800 border-b pb-3">
              Identificação da Operação
            </h2>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Nome da Operação <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.nome_operacao"
                type="text"
                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                :class="
                  erro('nome_operacao') ? 'border-red-500' : 'border-gray-300'
                "
                placeholder="Ex: Operação Luz Azul"
              />
              <div
                v-if="erro('nome_operacao')"
                class="text-red-500 text-sm mt-1"
              >
                {{ erro('nome_operacao') }}
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Nome da Autoridade Policial Responsável
                  <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.autoridade_responsavel_nome"
                  type="text"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                  :class="
                    erro('autoridade_responsavel_nome')
                      ? 'border-red-500'
                      : 'border-gray-300'
                  "
                />
                <div
                  v-if="erro('autoridade_responsavel_nome')"
                  class="text-red-500 text-sm mt-1"
                >
                  {{ erro('autoridade_responsavel_nome') }}
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Matrícula da Autoridade <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.autoridade_responsavel_matricula"
                  type="text"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                  :class="
                    erro('autoridade_responsavel_matricula')
                      ? 'border-red-500'
                      : 'border-gray-300'
                  "
                />
                <div
                  v-if="erro('autoridade_responsavel_matricula')"
                  class="text-red-500 text-sm mt-1"
                >
                  {{ erro('autoridade_responsavel_matricula') }}
                </div>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <!-- Origem da Operação -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Origem da Operação <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.origem_operacao"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                  :class="
                    erro('origem_operacao')
                      ? 'border-red-500'
                      : 'border-gray-300'
                  "
                >
                  <option value="">Selecione...</option>
                  <option
                    v-for="(label, value) in opcoes.origens"
                    :key="value"
                    :value="value"
                  >
                    {{ label }}
                  </option>
                </select>
                <p
                  v-if="form.origem_operacao === 'Alvo em outro Estado'"
                  class="mt-1 text-xs text-blue-600"
                >
                  A PCPB é responsável pela operação, mas o alvo está em
                  outro(s) estado(s).
                </p>
                <div
                  v-if="erro('origem_operacao')"
                  class="text-red-500 text-sm mt-1"
                >
                  {{ erro('origem_operacao') }}
                </div>
              </div>

              <!-- UF Responsável pela Operação (sempre visível) -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  UF Responsável pela Operação
                  <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.uf_responsavel"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                  :class="
                    erro('uf_responsavel')
                      ? 'border-red-500'
                      : 'border-gray-300'
                  "
                >
                  <option
                    v-for="(nome, sigla) in opcoes.ufs"
                    :key="sigla"
                    :value="sigla"
                  >
                    {{ sigla }} - {{ nome }}
                  </option>
                </select>
                <div
                  v-if="erro('uf_responsavel')"
                  class="text-red-500 text-sm mt-1"
                >
                  {{ erro('uf_responsavel') }}
                </div>
              </div>

              <!-- UFs do Alvo — aparece SOMENTE quando origem = "Alvo em outro Estado" -->
              <div
                v-if="form.origem_operacao === 'Alvo em outro Estado'"
                class="md:col-span-3 border border-blue-200 bg-blue-50 rounded-lg p-4"
              >
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Estado(s) e Quantidade de Alvos por Estado
                  <span class="text-red-500">*</span>
                </label>

                <!-- Linha de adição -->
                <div class="flex gap-2 mb-3">
                  <select
                    v-model="ufAlvoSelecionada"
                    class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent bg-white"
                  >
                    <option value="">Selecione o estado...</option>
                    <option v-for="uf in ufsDisponiveis" :key="uf" :value="uf">
                      {{ uf }} — {{ opcoes.ufs[uf] }}
                    </option>
                  </select>
                  <input
                    v-model.number="ufAlvoQuantidade"
                    type="number"
                    min="1"
                    placeholder="Qtd"
                    class="w-24 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                  />
                  <button
                    type="button"
                    @click="adicionarUfAlvo"
                    :disabled="!ufAlvoSelecionada || ufAlvoQuantidade < 1"
                    class="px-4 py-2 bg-gray-700 text-white rounded-lg hover:bg-gray-800 transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                  >
                    + Adicionar
                  </button>
                </div>

                <!-- Lista de UFs adicionadas -->
                <div
                  v-if="Object.keys(form.ufs_alvo_outros_estados).length > 0"
                  class="space-y-2 mb-2"
                >
                  <div
                    v-for="(qtd, uf) in form.ufs_alvo_outros_estados"
                    :key="uf"
                    class="flex items-center justify-between bg-white border border-blue-200 rounded-lg px-3 py-2"
                  >
                    <div class="flex items-center gap-3">
                      <span class="font-bold text-blue-800 text-sm">{{
                        uf
                      }}</span>
                      <span class="text-gray-600 text-sm">{{
                        opcoes.ufs[uf]
                      }}</span>
                      <span
                        class="bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-0.5 rounded-full border border-blue-300"
                      >
                        {{ qtd }} {{ qtd === 1 ? 'alvo' : 'alvos' }}
                      </span>
                    </div>
                    <button
                      type="button"
                      @click="removerUfAlvo(uf)"
                      class="text-red-500 hover:text-red-700 text-xs font-medium"
                    >
                      Remover
                    </button>
                  </div>
                  <div class="flex justify-end pt-1">
                    <span class="text-sm text-gray-600">
                      Total fora da PB:
                      <strong class="text-gray-900">{{
                        form.quantidade_alvos_outros_estados
                      }}</strong>
                    </span>
                  </div>
                </div>
                <p v-else class="text-sm text-gray-400 italic">
                  Nenhum estado adicionado ainda.
                </p>

                <div
                  v-if="erro('ufs_alvo_outros_estados')"
                  class="text-red-500 text-sm mt-2"
                >
                  {{ erro('ufs_alvo_outros_estados') }}
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Data da Operação <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.data_operacao"
                  type="date"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                  :class="
                    erro('data_operacao') ? 'border-red-500' : 'border-gray-300'
                  "
                />
                <div
                  v-if="erro('data_operacao')"
                  class="text-red-500 text-sm mt-1"
                >
                  {{ erro('data_operacao') }}
                </div>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Cidade(s) Alvo da Operação <span class="text-red-500">*</span>
                </label>
                <textarea
                  v-model="form.cidades_alvo"
                  rows="3"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                  :class="
                    erro('cidades_alvo') ? 'border-red-500' : 'border-gray-300'
                  "
                  placeholder="Ex: João Pessoa, Campina Grande, Patos"
                ></textarea>
                <div
                  v-if="erro('cidades_alvo')"
                  class="text-red-500 text-sm mt-1"
                >
                  {{ erro('cidades_alvo') }}
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Crime(s) Investigado(s) <span class="text-red-500">*</span>
                </label>
                <textarea
                  v-model="form.crimes_investigados"
                  rows="3"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                  :class="
                    erro('crimes_investigados')
                      ? 'border-red-500'
                      : 'border-gray-300'
                  "
                  placeholder="Ex: Tráfico de drogas, Homicídio qualificado"
                ></textarea>
                <div
                  v-if="erro('crimes_investigados')"
                  class="text-red-500 text-sm mt-1"
                >
                  {{ erro('crimes_investigados') }}
                </div>
              </div>
            </div>
          </div>

          <!-- ETAPA 2: Briefing -->
          <div v-show="etapaAtual === 2" class="space-y-6">
            <h2 class="text-xl font-semibold text-gray-800 border-b pb-3">
              Informações do Briefing
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Local do Briefing <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.local_briefing"
                  type="text"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                  :class="
                    erro('local_briefing')
                      ? 'border-red-500'
                      : 'border-gray-300'
                  "
                  placeholder="Ex: ACADEPOL"
                />
                <div
                  v-if="erro('local_briefing')"
                  class="text-red-500 text-sm mt-1"
                >
                  {{ erro('local_briefing') }}
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Horário do Briefing <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.horario_briefing"
                  type="time"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                  :class="
                    erro('horario_briefing')
                      ? 'border-red-500'
                      : 'border-gray-300'
                  "
                />
                <div
                  v-if="erro('horario_briefing')"
                  class="text-red-500 text-sm mt-1"
                >
                  {{ erro('horario_briefing') }}
                </div>
              </div>
            </div>
          </div>

          <!-- ETAPA 3: Mandados e Recursos -->
          <div v-show="etapaAtual === 3" class="space-y-6">
            <h2 class="text-xl font-semibold text-gray-800 border-b pb-3">
              Mandados, Alvos e Recursos (Quantidade)
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Total de Alvos (Buscas e Prisões)
                  <span class="text-red-500">*</span>
                </label>
                <input
                  v-model.number="form.quantidade_total_alvos"
                  type="number"
                  min="0"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                  :class="
                    erro('quantidade_total_alvos')
                      ? 'border-red-500'
                      : 'border-gray-300'
                  "
                />
                <div
                  v-if="erro('quantidade_total_alvos')"
                  class="text-red-500 text-sm mt-1"
                >
                  {{ erro('quantidade_total_alvos') }}
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Mandados de Prisão <span class="text-red-500">*</span>
                </label>
                <input
                  v-model.number="form.quantidade_mandados_prisao"
                  type="number"
                  min="0"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                  :class="
                    erro('quantidade_mandados_prisao')
                      ? 'border-red-500'
                      : 'border-gray-300'
                  "
                />
                <div
                  v-if="erro('quantidade_mandados_prisao')"
                  class="text-red-500 text-sm mt-1"
                >
                  {{ erro('quantidade_mandados_prisao') }}
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Mandados de Busca e Apreensão
                  <span class="text-red-500">*</span>
                </label>
                <input
                  v-model.number="form.quantidade_mandados_busca_apreensao"
                  type="number"
                  min="0"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                  :class="
                    erro('quantidade_mandados_busca_apreensao')
                      ? 'border-red-500'
                      : 'border-gray-300'
                  "
                />
                <div
                  v-if="erro('quantidade_mandados_busca_apreensao')"
                  class="text-red-500 text-sm mt-1"
                >
                  {{ erro('quantidade_mandados_busca_apreensao') }}
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Mandados de Busca e Apreensão de Infrator
                  <span class="text-red-500">*</span>
                </label>
                <input
                  v-model.number="
                    form.quantidade_mandados_busca_apreensao_infrator
                  "
                  type="number"
                  min="0"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                  :class="
                    erro('quantidade_mandados_busca_apreensao_infrator')
                      ? 'border-red-500'
                      : 'border-gray-300'
                  "
                />
                <div
                  v-if="erro('quantidade_mandados_busca_apreensao_infrator')"
                  class="text-red-500 text-sm mt-1"
                >
                  {{ erro('quantidade_mandados_busca_apreensao_infrator') }}
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Alvos em Outros Estados <span class="text-red-500">*</span>
                </label>
                <!-- Quando "Alvo em outro Estado": calculado automaticamente pelas UFs cadastradas -->
                <input
                  v-if="form.origem_operacao !== 'Alvo em outro Estado'"
                  v-model.number="form.quantidade_alvos_outros_estados"
                  type="number"
                  min="0"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                  :class="
                    erro('quantidade_alvos_outros_estados')
                      ? 'border-red-500'
                      : 'border-gray-300'
                  "
                />
                <div
                  v-else
                  class="w-full px-4 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-700 font-medium"
                >
                  {{ form.quantidade_alvos_outros_estados }}
                  <span class="text-xs text-gray-400 ml-1"
                    >(calculado automaticamente)</span
                  >
                </div>
                <div
                  v-if="erro('quantidade_alvos_outros_estados')"
                  class="text-red-500 text-sm mt-1"
                >
                  {{ erro('quantidade_alvos_outros_estados') }}
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Policiais Civis Empregados <span class="text-red-500">*</span>
                </label>
                <input
                  v-model.number="form.quantidade_policiais_empregados"
                  type="number"
                  min="1"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                  :class="
                    erro('quantidade_policiais_empregados')
                      ? 'border-red-500'
                      : 'border-gray-300'
                  "
                />
                <div
                  v-if="erro('quantidade_policiais_empregados')"
                  class="text-red-500 text-sm mt-1"
                >
                  {{ erro('quantidade_policiais_empregados') }}
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Viaturas da Polícia Civil Empregadas
                  <span class="text-red-500">*</span>
                </label>
                <input
                  v-model.number="form.quantidade_viaturas_empregadas"
                  type="number"
                  min="0"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                  :class="
                    erro('quantidade_viaturas_empregadas')
                      ? 'border-red-500'
                      : 'border-gray-300'
                  "
                />
                <div
                  v-if="erro('quantidade_viaturas_empregadas')"
                  class="text-red-500 text-sm mt-1"
                >
                  {{ erro('quantidade_viaturas_empregadas') }}
                </div>
              </div>
            </div>
          </div>

          <!-- ETAPA 4: Vinculações -->
          <div v-show="etapaAtual === 4" class="space-y-6">
            <h2 class="text-xl font-semibold text-gray-800 border-b pb-3">
              Vinculações Institucionais
            </h2>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                A Operação está vinculada a qual Unidade?
                <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.vinculada_unidade"
                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                :class="
                  erro('vinculada_unidade')
                    ? 'border-red-500'
                    : 'border-gray-300'
                "
              >
                <option value="">Selecione...</option>
                <option
                  v-for="unidade in opcoes.unidades"
                  :key="unidade"
                  :value="unidade"
                >
                  {{ unidade }}
                </option>
              </select>
              <div
                v-if="erro('vinculada_unidade')"
                class="text-red-500 text-sm mt-1"
              >
                {{ erro('vinculada_unidade') }}
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                A Operação está vinculada a qual Unidade Especializada?
                <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.vinculada_unidade_especializada"
                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                :class="
                  erro('vinculada_unidade_especializada')
                    ? 'border-red-500'
                    : 'border-gray-300'
                "
              >
                <option value="">Selecione...</option>
                <option
                  v-for="(label, value) in opcoes.unidades_especializadas"
                  :key="value"
                  :value="value"
                >
                  {{ label }}
                </option>
              </select>
              <div
                v-if="erro('vinculada_unidade_especializada')"
                class="text-red-500 text-sm mt-1"
              >
                {{ erro('vinculada_unidade_especializada') }}
              </div>
            </div>

            <!-- Campo condicional -->
            <div v-if="mostrarOutraUnidade">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Especifique a Unidade Policial
                <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.outra_unidade_policial"
                type="text"
                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                :class="
                  erro('outra_unidade_policial')
                    ? 'border-red-500'
                    : 'border-gray-300'
                "
                placeholder="Informe o nome da unidade policial"
              />
              <div
                v-if="erro('outra_unidade_policial')"
                class="text-red-500 text-sm mt-1"
              >
                {{ erro('outra_unidade_policial') }}
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                A Operação está vinculada a qual Delegacia Seccional?
                <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.vinculada_delegacia_seccional"
                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                :class="
                  erro('vinculada_delegacia_seccional')
                    ? 'border-red-500'
                    : 'border-gray-300'
                "
              >
                <option value="">Selecione...</option>
                <option
                  v-for="delegacia in opcoes.delegacias"
                  :key="delegacia"
                  :value="delegacia"
                >
                  {{ delegacia }}
                </option>
              </select>
              <div
                v-if="erro('vinculada_delegacia_seccional')"
                class="text-red-500 text-sm mt-1"
              >
                {{ erro('vinculada_delegacia_seccional') }}
              </div>
            </div>
          </div>

          <!-- ETAPA 5: Solicitação de Apoio -->
          <div v-show="etapaAtual === 5" class="space-y-6">
            <h2 class="text-xl font-semibold text-gray-800 border-b pb-3">
              Solicitação de Apoio à DIOP
            </h2>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Especifique a Solicitação (Opcional)
              </label>
              <p class="text-sm text-gray-500 mb-2">
                Servidores, composição da equipe, recursos especiais, apoio
                logístico, entre outros.
              </p>
              <p class="text-sm text-gray-500 mb-2">
                Obs.: Em caso de alvo do sexo feminino, informar a necessidade
                de policial do sexo feminino.
              </p>
              <textarea
                v-model="form.solicitacao_apoio_diop"
                rows="5"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                placeholder="Descreva aqui a solicitação de apoio..."
              ></textarea>
            </div>

            <!-- Resumo final -->
            <div class="bg-gray-100 border border-gray-200 rounded-lg p-4 mt-6">
              <h3 class="font-semibold text-gray-900 mb-2">✓ Revisão Final</h3>
              <p class="text-sm text-gray-700">
                Revise todas as informações antes de salvar. Após o envio, você
                poderá gerar o PDF da operação.
              </p>
            </div>
          </div>

          <!-- Botões de Navegação -->
          <div class="flex justify-between items-center mt-8 pt-6 border-t">
            <button
              v-if="etapaAtual > 1"
              type="button"
              @click="voltarEtapa"
              class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
            >
              ← Voltar
            </button>
            <div v-else></div>

            <div class="flex gap-3">
              <button
                v-if="etapaAtual < totalEtapas"
                type="button"
                @click="proximaEtapa"
                class="px-6 py-3 bg-[#bea55a] text-white rounded-lg hover:bg-[#968143] transition-colors"
              >
                Próxima →
              </button>

              <button
                type="button"
                @click="abrirModalJustificativa"
                :disabled="form.processing"
                class="px-6 py-2 bg-[#bea55a] text-white rounded-lg hover:bg-[#968143] transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="!form.processing">✓ Atualizar Operação</span>
                <span v-else>Atualizando...</span>
              </button>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
  <Teleport to="body">
    <div
      v-if="modalJustificativa"
      class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4"
    >
      <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg p-6">
        <div class="flex items-center gap-3 mb-4">
          <div>
            <h3 class="text-lg font-semibold text-gray-900">
              Justificativa da Alteração
            </h3>
            <p class="text-sm text-red-500">
              Ficará registrada no histórico de auditoria.
            </p>
          </div>
        </div>

        <label class="block text-sm font-medium text-gray-700 mb-2">
          Por que você está realizando esta alteração?
          <span class="text-red-500">*</span>
        </label>
        <textarea
          v-model="justificativaTexto"
          rows="4"
          maxlength="1000"
          placeholder="Descreva o motivo da alteração (mínimo 10 caracteres)..."
          class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-[#bea55a] focus:border-transparent resize-none"
          :class="justificativaErro ? 'border-red-500' : 'border-gray-300'"
        ></textarea>
        <div class="flex justify-between items-center mt-1 mb-4">
          <p v-if="justificativaErro" class="text-red-500 text-sm">
            {{ justificativaErro }}
          </p>
          <p v-else class="text-gray-400 text-xs">Mínimo 10 caracteres</p>
          <span class="text-gray-400 text-xs"
            >{{ justificativaTexto.length }}/1000</span
          >
        </div>

        <div class="flex justify-end gap-3">
          <button
            type="button"
            @click="modalJustificativa = false"
            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
          >
            Cancelar
          </button>
          <button
            type="button"
            @click="confirmarEdicao"
            :disabled="form.processing"
            class="px-6 py-2 bg-[#bea55a] text-white rounded-lg hover:bg-[#968143] transition-colors disabled:opacity-50"
          >
            <span v-if="!form.processing">Confirmar e Salvar</span>
            <span v-else>Salvando...</span>
          </button>
        </div>
      </div>
    </div>
  </Teleport>
  <Footer />
</template>

<style scoped>
input:focus,
select:focus,
textarea:focus {
  outline: none;
}
</style>
