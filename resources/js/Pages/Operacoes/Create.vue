<script setup>
import { ref, computed } from 'vue';
import { useForm, Head, Link } from '@inertiajs/vue3';
import Header from '../Components/Header.vue';
import SiteNavbar from '../Components/SiteNavbar.vue';
import Footer from '../Components/Footer.vue';

const props = defineProps({
  operacao: Object,
  opcoes: Object,
});

const isEdit = computed(() => !!props.operacao);

const form = useForm({
  nome_operacao: props.operacao?.nome_operacao || '',
  autoridade_responsavel_nome: props.operacao?.autoridade_responsavel_nome || '',
  autoridade_responsavel_matricula: props.operacao?.autoridade_responsavel_matricula || '',
  origem_operacao: props.operacao?.origem_operacao || '',
  uf_responsavel: props.operacao?.uf_responsavel || 'PB',
  data_operacao: props.operacao?.data_operacao || '',
  local_briefing: props.operacao?.local_briefing || '',
  horario_briefing: props.operacao?.horario_briefing?.substring(0, 5) || '',
  quantidade_total_alvos: props.operacao?.quantidade_total_alvos || 0,
  quantidade_mandados_prisao: props.operacao?.quantidade_mandados_prisao || 0,
  quantidade_mandados_busca_apreensao: props.operacao?.quantidade_mandados_busca_apreensao || 0,
  quantidade_mandados_busca_apreensao_infrator: props.operacao?.quantidade_mandados_busca_apreensao_infrator || 0,
  quantidade_alvos_outros_estados: props.operacao?.quantidade_alvos_outros_estados || 0,
  quantidade_policiais_empregados: props.operacao?.quantidade_policiais_empregados || 0,
  quantidade_viaturas_empregadas: props.operacao?.quantidade_viaturas_empregadas || 0,
  cidades_alvo: props.operacao?.cidades_alvo || '',
  crimes_investigados: props.operacao?.crimes_investigados || '',
  vinculada_unidade: props.operacao?.vinculada_unidade || '',
  vinculada_unidade_especializada: props.operacao?.vinculada_unidade_especializada || '',
  outra_unidade_policial: props.operacao?.outra_unidade_policial || '',
  vinculada_delegacia_seccional: props.operacao?.vinculada_delegacia_seccional || '',
  solicitacao_apoio_diop: props.operacao?.solicitacao_apoio_diop || '',
});

// Controle de etapas
const etapaAtual = ref(1);
const totalEtapas = 5;

// Erros de validação client-side por etapa
const errorsEtapa = ref({});

// Validação por etapa
const validarEtapa = (etapa) => {
  const erros = {};

  if (etapa === 1) {
    if (!form.nome_operacao?.trim())
      erros.nome_operacao = 'O nome da operação é obrigatório.';
    if (!form.autoridade_responsavel_nome?.trim())
      erros.autoridade_responsavel_nome = 'O nome da autoridade é obrigatório.';
    if (!form.autoridade_responsavel_matricula?.trim())
      erros.autoridade_responsavel_matricula = 'A matrícula da autoridade é obrigatória.';
    if (!form.origem_operacao)
      erros.origem_operacao = 'A origem da operação é obrigatória.';
    if (!form.uf_responsavel)
      erros.uf_responsavel = 'A UF responsável é obrigatória.';
    if (!form.data_operacao)
      erros.data_operacao = 'A data da operação é obrigatória.';
    if (!form.cidades_alvo?.trim())
      erros.cidades_alvo = 'As cidades alvo são obrigatórias.';
    if (!form.crimes_investigados?.trim())
      erros.crimes_investigados = 'Os crimes investigados são obrigatórios.';
  }

  if (etapa === 2) {
    if (!form.local_briefing?.trim())
      erros.local_briefing = 'O local do briefing é obrigatório.';
    if (!form.horario_briefing)
      erros.horario_briefing = 'O horário do briefing é obrigatório.';
  }

  if (etapa === 3) {
    if (form.quantidade_total_alvos === null || form.quantidade_total_alvos === '')
      erros.quantidade_total_alvos = 'A quantidade total de alvos é obrigatória.';
    if (form.quantidade_mandados_prisao === null || form.quantidade_mandados_prisao === '')
      erros.quantidade_mandados_prisao = 'A quantidade de mandados de prisão é obrigatória.';
    if (form.quantidade_mandados_busca_apreensao === null || form.quantidade_mandados_busca_apreensao === '')
      erros.quantidade_mandados_busca_apreensao = 'A quantidade de mandados de busca é obrigatória.';
    if (form.quantidade_mandados_busca_apreensao_infrator === null || form.quantidade_mandados_busca_apreensao_infrator === '')
      erros.quantidade_mandados_busca_apreensao_infrator = 'A quantidade de mandados de busca (infrator) é obrigatória.';
    if (form.quantidade_alvos_outros_estados === null || form.quantidade_alvos_outros_estados === '')
      erros.quantidade_alvos_outros_estados = 'A quantidade de alvos em outros estados é obrigatória.';
    if (form.quantidade_policiais_empregados === null || form.quantidade_policiais_empregados === '' || form.quantidade_policiais_empregados < 1)
      erros.quantidade_policiais_empregados = 'Deve haver pelo menos 1 policial empregado.';
    if (form.quantidade_viaturas_empregadas === null || form.quantidade_viaturas_empregadas === '')
      erros.quantidade_viaturas_empregadas = 'A quantidade de viaturas é obrigatória.';
  }

  if (etapa === 4) {
    if (!form.vinculada_unidade)
      erros.vinculada_unidade = 'A unidade vinculada é obrigatória.';
    if (!form.vinculada_unidade_especializada)
      erros.vinculada_unidade_especializada = 'A unidade especializada é obrigatória.';
    if (form.vinculada_unidade_especializada === 'OUTRA' && !form.outra_unidade_policial?.trim())
      erros.outra_unidade_policial = 'Especifique a unidade policial quando selecionar "OUTRA".';
    if (!form.vinculada_delegacia_seccional)
      erros.vinculada_delegacia_seccional = 'A delegacia seccional é obrigatória.';
  }

  // Etapa 5 não tem campos obrigatórios

  return erros;
};

const proximaEtapa = () => {
  const erros = validarEtapa(etapaAtual.value);
  errorsEtapa.value = erros;

  if (Object.keys(erros).length > 0) return; // bloqueia se houver erros

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

// Submit
const submit = () => {
  errorsEtapa.value = {};

  if (isEdit.value) {
    form.put(route('operacoes.update', props.operacao.id));
  } else {
    form.post(route('operacoes.store'));
  }
};

// Mostrar campo "outra unidade" apenas se necessário
const mostrarOutraUnidade = computed(() => {
  return form.vinculada_unidade_especializada === 'OUTRA';
});

// Helper: exibe erro do servidor OU da validação client-side
const erro = (campo) => {
  return form.errors[campo] || errorsEtapa.value[campo];
};
</script>

<template>
  <div>
    <Head>
      <title>{{ isEdit ? 'Editar Operação' : 'Nova Operação' }}</title>
    </Head>

    <SiteNavbar />
    <Header />

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
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Voltar para a Listagem
      </Link>
    </div>
    
    

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="bg-white shadow-xl rounded-2xl overflow-hidden border border-gray-100">
      <!-- Cabeçalho -->
      <div class="bg-white shadow rounded-lg mb-6 p-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">
              {{ isEdit ? 'Editar Operação' : 'Cadastrar Nova Operação' }}
            </h1>
            <p class="mt-2 text-sm text-gray-600">
              Preencha todos os campos obrigatórios para registrar a operação policial
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

      <!-- Formulário -->
      <form @submit.prevent="submit" novalidate>
        <div class="bg-white shadow rounded-lg p-6">
          <!-- ETAPA 1: Identificação da Operação -->
          <div v-show="etapaAtual === 1" class="space-y-6">
            <h2 class="text-xl font-semibold text-gray-800 border-b pb-3">
              Identificação da Operação
            </h2>

            <!-- Nome da Operação -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Nome da Operação <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.nome_operacao"
                type="text"
                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                :class="erro('nome_operacao') ? 'border-red-500' : 'border-gray-300'"
                placeholder="Ex: Operação Luz Azul"
              />
              <div v-if="erro('nome_operacao')" class="text-red-500 text-sm mt-1">
                {{ erro('nome_operacao') }}
              </div>
            </div>

            <!-- Grid 2 colunas -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Autoridade Responsável -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Nome da Autoridade Policial Responsável <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.autoridade_responsavel_nome"
                  type="text"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                  :class="erro('autoridade_responsavel_nome') ? 'border-red-500' : 'border-gray-300'"
                />
                <div v-if="erro('autoridade_responsavel_nome')" class="text-red-500 text-sm mt-1">
                  {{ erro('autoridade_responsavel_nome') }}
                </div>
              </div>

              <!-- Matrícula Autoridade -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Matrícula da Autoridade <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.autoridade_responsavel_matricula"
                  type="text"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                  :class="erro('autoridade_responsavel_matricula') ? 'border-red-500' : 'border-gray-300'"
                />
                <div v-if="erro('autoridade_responsavel_matricula')" class="text-red-500 text-sm mt-1">
                  {{ erro('autoridade_responsavel_matricula') }}
                </div>
              </div>
            </div>

            <!-- Grid 3 colunas -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <!-- Origem -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Origem da Operação <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.origem_operacao"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                  :class="erro('origem_operacao') ? 'border-red-500' : 'border-gray-300'"
                >
                  <option value="">Selecione...</option>
                  <option v-for="(label, value) in opcoes.origens" :key="value" :value="value">
                    {{ label }}
                  </option>
                </select>
                <div v-if="erro('origem_operacao')" class="text-red-500 text-sm mt-1">
                  {{ erro('origem_operacao') }}
                </div>
              </div>

              <!-- UF -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  UF Responsável pela Operação <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.uf_responsavel"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                  :class="erro('uf_responsavel') ? 'border-red-500' : 'border-gray-300'"
                >
                  <option v-for="(nome, sigla) in opcoes.ufs" :key="sigla" :value="sigla">
                    {{ sigla }} - {{ nome }}
                  </option>
                </select>
                <div v-if="erro('uf_responsavel')" class="text-red-500 text-sm mt-1">
                  {{ erro('uf_responsavel') }}
                </div>
              </div>

              <!-- Data -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Data da Operação <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.data_operacao"
                  type="date"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                  :class="erro('data_operacao') ? 'border-red-500' : 'border-gray-300'"
                />
                <div v-if="erro('data_operacao')" class="text-red-500 text-sm mt-1">
                  {{ erro('data_operacao') }}
                </div>
              </div>
            </div>

            <!-- Cidades e Crimes -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Cidade(s) Alvo da Operação <span class="text-red-500">*</span>
                </label>
                <textarea
                  v-model="form.cidades_alvo"
                  rows="3"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                  :class="erro('cidades_alvo') ? 'border-red-500' : 'border-gray-300'"
                  placeholder="Ex: João Pessoa, Campina Grande, Patos"
                ></textarea>
                <div v-if="erro('cidades_alvo')" class="text-red-500 text-sm mt-1">
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
                  :class="erro('crimes_investigados') ? 'border-red-500' : 'border-gray-300'"
                  placeholder="Ex: Tráfico de drogas, Homicídio qualificado"
                ></textarea>
                <div v-if="erro('crimes_investigados')" class="text-red-500 text-sm mt-1">
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
                  :class="erro('local_briefing') ? 'border-red-500' : 'border-gray-300'"
                  placeholder="Ex: ACADEPOL"
                />
                <div v-if="erro('local_briefing')" class="text-red-500 text-sm mt-1">
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
                  :class="erro('horario_briefing') ? 'border-red-500' : 'border-gray-300'"
                />
                <div v-if="erro('horario_briefing')" class="text-red-500 text-sm mt-1">
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

            <!-- Quantidades -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Total de Alvos (Buscas e Prisões) <span class="text-red-500">*</span>
                </label>
                <input
                  v-model.number="form.quantidade_total_alvos"
                  type="number"
                  min="0"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                  :class="erro('quantidade_total_alvos') ? 'border-red-500' : 'border-gray-300'"
                />
                <div v-if="erro('quantidade_total_alvos')" class="text-red-500 text-sm mt-1">
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
                  :class="erro('quantidade_mandados_prisao') ? 'border-red-500' : 'border-gray-300'"
                />
                <div v-if="erro('quantidade_mandados_prisao')" class="text-red-500 text-sm mt-1">
                  {{ erro('quantidade_mandados_prisao') }}
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Mandados de Busca e Apreensão <span class="text-red-500">*</span>
                </label>
                <input
                  v-model.number="form.quantidade_mandados_busca_apreensao"
                  type="number"
                  min="0"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                  :class="erro('quantidade_mandados_busca_apreensao') ? 'border-red-500' : 'border-gray-300'"
                />
                <div v-if="erro('quantidade_mandados_busca_apreensao')" class="text-red-500 text-sm mt-1">
                  {{ erro('quantidade_mandados_busca_apreensao') }}
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Mandados de Busca e Apreensão de Infrator <span class="text-red-500">*</span>
                </label>
                <input
                  v-model.number="form.quantidade_mandados_busca_apreensao_infrator"
                  type="number"
                  min="0"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                  :class="erro('quantidade_mandados_busca_apreensao_infrator') ? 'border-red-500' : 'border-gray-300'"
                />
                <div v-if="erro('quantidade_mandados_busca_apreensao_infrator')" class="text-red-500 text-sm mt-1">
                  {{ erro('quantidade_mandados_busca_apreensao_infrator') }}
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Alvos em Outros Estados <span class="text-red-500">*</span>
                </label>
                <input
                  v-model.number="form.quantidade_alvos_outros_estados"
                  type="number"
                  min="0"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                  :class="erro('quantidade_alvos_outros_estados') ? 'border-red-500' : 'border-gray-300'"
                />
                <div v-if="erro('quantidade_alvos_outros_estados')" class="text-red-500 text-sm mt-1">
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
                  :class="erro('quantidade_policiais_empregados') ? 'border-red-500' : 'border-gray-300'"
                />
                <div v-if="erro('quantidade_policiais_empregados')" class="text-red-500 text-sm mt-1">
                  {{ erro('quantidade_policiais_empregados') }}
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Viaturas da Polícia Civil Empregadas <span class="text-red-500">*</span>
                </label>
                <input
                  v-model.number="form.quantidade_viaturas_empregadas"
                  type="number"
                  min="0"
                  class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                  :class="erro('quantidade_viaturas_empregadas') ? 'border-red-500' : 'border-gray-300'"
                />
                <div v-if="erro('quantidade_viaturas_empregadas')" class="text-red-500 text-sm mt-1">
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
                A Operação está vinculada a qual Unidade? <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.vinculada_unidade"
                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                :class="erro('vinculada_unidade') ? 'border-red-500' : 'border-gray-300'"
              >
                <option value="">Selecione...</option>
                <option v-for="unidade in opcoes.unidades" :key="unidade" :value="unidade">
                  {{ unidade }}
                </option>
              </select>
              <div v-if="erro('vinculada_unidade')" class="text-red-500 text-sm mt-1">
                {{ erro('vinculada_unidade') }}
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                A Operação está vinculada a qual Unidade Especializada?  <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.vinculada_unidade_especializada"
                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                :class="erro('vinculada_unidade_especializada') ? 'border-red-500' : 'border-gray-300'"
              >
                <option value="">Selecione...</option>
                <option v-for="(label, value) in opcoes.unidades_especializadas" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
              <div v-if="erro('vinculada_unidade_especializada')" class="text-red-500 text-sm mt-1">
                {{ erro('vinculada_unidade_especializada') }}
              </div>
            </div>

            <!-- Campo condicional -->
            <div v-if="mostrarOutraUnidade">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Especifique a Unidade Policial <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.outra_unidade_policial"
                type="text"
                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                :class="erro('outra_unidade_policial') ? 'border-red-500' : 'border-gray-300'"
                placeholder="Informe o nome da unidade policial"
              />
              <div v-if="erro('outra_unidade_policial')" class="text-red-500 text-sm mt-1">
                {{ erro('outra_unidade_policial') }}
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                A Operação está vinculada a qual Delegacia Seccional? <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.vinculada_delegacia_seccional"
                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                :class="erro('vinculada_delegacia_seccional') ? 'border-red-500' : 'border-gray-300'"
              >
                <option value="">Selecione...</option>
                <option v-for="delegacia in opcoes.delegacias" :key="delegacia" :value="delegacia">
                  {{ delegacia }}
                </option>
              </select>
              <div v-if="erro('vinculada_delegacia_seccional')" class="text-red-500 text-sm mt-1">
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
                Servidores, formatação da equipe, recursos especiais, lanches e outros
              </p>
              <textarea
                v-model="form.solicitacao_apoio_diop"
                rows="5"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-gray-500 focus:border-transparent"
                placeholder="Descreva aqui a solicitação de apoio..."
              ></textarea>
              <div v-if="erro('solicitacao_apoio_diop')" class="text-red-500 text-sm mt-1">
                {{ erro('solicitacao_apoio_diop') }}
              </div>
            </div>

            <!-- Resumo final -->
            <div class="bg-gray-100 border border-gray-200 rounded-lg p-4 mt-6">
              <h3 class="font-semibold text-neutral-900 mb-2">✓ Revisão Final</h3>
              <p class="text-sm text-neutral-700">
                Revise todas as informações antes de salvar. Após o envio, você poderá gerar o PDF da operação.
              </p>
            </div>
          </div>

          <!-- Botões de Navegação -->
          <div class="flex justify-between items-center mt-8 pt-6 border-t">
            <button
              v-if="etapaAtual > 1"
              type="button"
              @click="voltarEtapa"
              class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
            >
              ← Voltar
            </button>
            <div v-else></div>

            <div class="flex gap-3">
              <button
                v-if="etapaAtual < totalEtapas"
                type="button"
                @click="proximaEtapa"
                class="px-6 py-2 bg-[#bea55a] text-white rounded-lg hover:bg-[#968143] transition-colors"
              >
                Próxima →
              </button>

              <button
                v-if="etapaAtual === totalEtapas"
                type="submit"
                :disabled="form.processing"
                class="px-6 py-2 bg-[#bea55a] text-white rounded-lg hover:bg-[#968143] transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="!form.processing">
                  {{ isEdit ? '✓ Atualizar Operação' : '✓ Cadastrar Operação' }}
                </span>
                <span v-else>Salvando...</span>
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
input:focus,
select:focus,
textarea:focus {
  outline: none;
}
</style>