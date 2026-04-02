<script setup>
import { Head, Link } from '@inertiajs/vue3';
import {
  mdiClipboardText,
  mdiArrowLeft,
  mdiAccount,
  mdiCalendar,
  mdiMapMarker,
  mdiShieldStar,
  mdiAlertCircleOutline,
  mdiCheckCircle,
} from '@mdi/js';
import LayoutAuthenticated from '@/Layouts/Admin/LayoutAuthenticated.vue';
import SectionMain from '@/Components/SectionMain.vue';
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue';
import CardBox from '@/Components/CardBox.vue';
import BaseButton from '@/Components/BaseButton.vue';
import BaseIcon from '@/Components/BaseIcon.vue';

const props = defineProps({
  operacao: { type: Object, required: true },
  estatisticas: { type: Object, required: true },
  tiposArma: { type: Object, default: () => ({}) },
  tiposEntorpecente: { type: Object, default: () => ({}) },
});

function formatarData(data) {
  if (!data) return '-';
  return new Date(data).toLocaleDateString('pt-BR', { timeZone: 'UTC' });
}

function formatarDataHora(iso) {
  if (!iso) return '-';
  return new Date(iso).toLocaleString('pt-BR');
}

function formatarHora(hora) {
  if (!hora) return '-';
  if (typeof hora === 'string' && hora.match(/^\d{2}:\d{2}/)) {
    return hora.substring(0, 5);
  }
  return hora;
}

function origemClass(origem) {
  const map = {
    Nacional: 'bg-blue-100 text-blue-800',
    Estadual: 'bg-green-100 text-green-800',
    'Apoio a outro Estado': 'bg-yellow-100 text-yellow-800',
    'Alvo em outro Estado': 'bg-orange-100 text-orange-800',
  };
  return map[origem] ?? 'bg-gray-100 text-gray-700';
}

function formatarDinheiro(valor) {
  if (!valor) return 'R$ 0,00';
  return 'R$ ' + Number(valor).toLocaleString('pt-BR', { minimumFractionDigits: 2 });
}
</script>

<template>
  <LayoutAuthenticated>
    <Head :title="`Operação: ${operacao.nome_operacao}`" />
    <SectionMain>
      <SectionTitleLineWithButton
        :icon="mdiClipboardText"
        :title="operacao.nome_operacao"
        main
      >
        <BaseButton
          :icon="mdiArrowLeft"
          label="Voltar"
          color="whiteDark"
          :route-name="route('admin.operacoes-admin.index')"
          small
        />
      </SectionTitleLineWithButton>

      <!-- Tags de status -->
      <div class="flex flex-wrap gap-2 mb-6">
        <span
          class="inline-block px-3 py-1 rounded-full text-xs font-semibold"
          :class="origemClass(operacao.origem_operacao)"
        >
          {{ operacao.origem_operacao }}
        </span>

        <span
          v-if="operacao.solicitacao_apoio_diop"
          class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 border border-amber-300"
        >
          <BaseIcon :path="mdiAlertCircleOutline" size="14" />
          Solicita Apoio DIOP
        </span>

        <span
          v-if="operacao.resultado"
          class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800"
        >
          <BaseIcon :path="mdiCheckCircle" size="14" />
          Resultado registrado
        </span>
        <span
          v-else
          class="inline-block px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600"
        >
          Sem resultado
        </span>
      </div>

      <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        <!-- Informações básicas -->
        <CardBox>
          <h3 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <BaseIcon :path="mdiCalendar" size="18" class="text-blue-500" />
            Informações da Operação
          </h3>
          <dl class="space-y-3 text-sm">
            <div class="flex justify-between border-b pb-2">
              <dt class="text-gray-500 font-medium">ID</dt>
              <dd class="text-gray-800 font-mono">#{{ operacao.id }}</dd>
            </div>
            <div class="flex justify-between border-b pb-2">
              <dt class="text-gray-500 font-medium">Data da Operação</dt>
              <dd class="text-gray-800">{{ formatarData(operacao.data_operacao) }}</dd>
            </div>
            <div class="flex justify-between border-b pb-2">
              <dt class="text-gray-500 font-medium">Origem</dt>
              <dd class="text-gray-800">{{ operacao.origem_operacao }}</dd>
            </div>
            <div class="flex justify-between border-b pb-2">
              <dt class="text-gray-500 font-medium">UF Responsável</dt>
              <dd class="text-gray-800">{{ operacao.uf_responsavel }}</dd>
            </div>
            <div
              v-if="operacao.ufs_alvo_outros_estados && Object.keys(operacao.ufs_alvo_outros_estados ?? {}).length"
              class="border-b pb-2"
            >
              <dt class="text-gray-500 font-medium mb-2">UFs Alvo</dt>
              <div class="flex flex-wrap gap-1">
                <span
                  v-for="(qtd, uf) in operacao.ufs_alvo_outros_estados"
                  :key="uf"
                  class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800"
                >
                  {{ uf }} ({{ qtd }})
                </span>
              </div>
            </div>
            <div class="flex justify-between border-b pb-2">
              <dt class="text-gray-500 font-medium">Unidade Responsável</dt>
              <dd class="text-gray-800 text-right">{{ operacao.unidade_policial_responsavel }}</dd>
            </div>
            <div class="flex justify-between border-b pb-2">
              <dt class="text-gray-500 font-medium">Cidades-Alvo</dt>
              <dd class="text-gray-800 text-right max-w-xs">{{ operacao.cidades_alvo }}</dd>
            </div>
            <div class="flex justify-between pb-2">
              <dt class="text-gray-500 font-medium">Crimes Investigados</dt>
              <dd class="text-gray-800 text-right max-w-xs">{{ operacao.crimes_investigados }}</dd>
            </div>
          </dl>
        </CardBox>

        <!-- Responsáveis + Briefing -->
        <CardBox>
          <h3 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <BaseIcon :path="mdiAccount" size="18" class="text-indigo-500" />
            Responsáveis e Briefing
          </h3>
          <dl class="space-y-3 text-sm">
            <div class="flex justify-between border-b pb-2">
              <dt class="text-gray-500 font-medium">Autoridade Responsável</dt>
              <dd class="text-gray-800 text-right">
                {{ operacao.autoridade_responsavel_nome }}
                <span class="block text-xs text-gray-400">Mat. {{ operacao.autoridade_responsavel_matricula }}</span>
              </dd>
            </div>
            <div class="flex justify-between border-b pb-2">
              <dt class="text-gray-500 font-medium">Policial Responsável</dt>
              <dd class="text-gray-800 text-right">
                {{ operacao.policial_responsavel_nome }}
                <span class="block text-xs text-gray-400">Mat. {{ operacao.policial_responsavel_matricula }}</span>
              </dd>
            </div>
            <div class="flex justify-between border-b pb-2">
              <dt class="text-gray-500 font-medium">Cadastrado por</dt>
              <dd class="text-gray-800 text-right">
                {{ operacao.user?.name ?? '-' }}
                <span v-if="operacao.user?.matricula" class="block text-xs text-gray-400">Mat. {{ operacao.user.matricula }}</span>
              </dd>
            </div>
            <div class="flex justify-between border-b pb-2">
              <dt class="text-gray-500 font-medium">Local do Briefing</dt>
              <dd class="text-gray-800 text-right">{{ operacao.local_briefing ?? '-' }}</dd>
            </div>
            <div class="flex justify-between border-b pb-2">
              <dt class="text-gray-500 font-medium">Horário do Briefing</dt>
              <dd class="text-gray-800">{{ formatarHora(operacao.horario_briefing) }}</dd>
            </div>
            <div class="flex justify-between border-b pb-2">
              <dt class="text-gray-500 font-medium">Vinc. Unidade</dt>
              <dd class="text-gray-800">{{ operacao.vinculada_unidade ?? '-' }}</dd>
            </div>
            <div class="flex justify-between border-b pb-2">
              <dt class="text-gray-500 font-medium">Unidade Especializada</dt>
              <dd class="text-gray-800">{{ operacao.vinculada_unidade_especializada ?? '-' }}</dd>
            </div>
            <div class="flex justify-between pb-2">
              <dt class="text-gray-500 font-medium">Delegacia Seccional</dt>
              <dd class="text-gray-800">{{ operacao.vinculada_delegacia_seccional ?? '-' }}</dd>
            </div>
          </dl>
        </CardBox>

        <!-- Estatísticas planejadas -->
        <CardBox>
          <h3 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <BaseIcon :path="mdiShieldStar" size="18" class="text-purple-500" />
            Estatísticas Planejadas
          </h3>
          <div class="grid grid-cols-2 gap-3">
            <div class="bg-gray-50 rounded-lg p-3 text-center">
              <p class="text-xs text-gray-500 font-medium">Total de Alvos</p>
              <p class="text-2xl font-bold text-gray-800 mt-1">{{ estatisticas.total_alvos }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3 text-center">
              <p class="text-xs text-gray-500 font-medium">Total de Mandados</p>
              <p class="text-2xl font-bold text-gray-800 mt-1">{{ estatisticas.total_mandados }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3 text-center">
              <p class="text-xs text-gray-500 font-medium">Mandados Prisão</p>
              <p class="text-2xl font-bold text-gray-800 mt-1">{{ estatisticas.mandados_prisao }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3 text-center">
              <p class="text-xs text-gray-500 font-medium">Mandados Busca</p>
              <p class="text-2xl font-bold text-gray-800 mt-1">{{ estatisticas.mandados_busca }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3 text-center">
              <p class="text-xs text-gray-500 font-medium">Policiais</p>
              <p class="text-2xl font-bold text-gray-800 mt-1">{{ estatisticas.policiais_empregados }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3 text-center">
              <p class="text-xs text-gray-500 font-medium">Viaturas</p>
              <p class="text-2xl font-bold text-gray-800 mt-1">{{ estatisticas.viaturas_empregadas }}</p>
            </div>
          </div>
        </CardBox>

        <!-- Solicitação de Apoio DIOP -->
        <CardBox v-if="operacao.solicitacao_apoio_diop">
          <h3 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <BaseIcon :path="mdiAlertCircleOutline" size="18" class="text-amber-500" />
            Solicitação de Apoio à DIOP
          </h3>
          <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
            <p class="text-gray-800 text-sm whitespace-pre-line">
              {{ operacao.solicitacao_apoio_diop }}
            </p>
          </div>
        </CardBox>

      </div>

      <!-- Resultado da Operação -->
      <div v-if="operacao.resultado" class="mt-6">
        <CardBox>
          <h3 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <BaseIcon :path="mdiCheckCircle" size="18" class="text-green-500" />
            Resultado / Debriefing
          </h3>

          <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

            <!-- Mandados cumpridos -->
            <div>
              <h4 class="text-sm font-semibold text-gray-600 mb-3 uppercase tracking-wide">Mandados</h4>
              <dl class="space-y-2 text-sm">
                <div class="flex justify-between border-b pb-2">
                  <dt class="text-gray-500">Prisão cumpridos</dt>
                  <dd class="font-semibold text-gray-800">{{ operacao.resultado.mandados_prisao_cumpridos }}</dd>
                </div>
                <div class="flex justify-between border-b pb-2">
                  <dt class="text-gray-500">Prisão não cumpridos</dt>
                  <dd class="font-semibold text-gray-800">{{ operacao.resultado.mandados_prisao_nao_cumpridos }}</dd>
                </div>
                <div class="flex justify-between border-b pb-2">
                  <dt class="text-gray-500">Busca cumpridos</dt>
                  <dd class="font-semibold text-gray-800">{{ operacao.resultado.mandados_busca_cumpridos }}</dd>
                </div>
                <div class="flex justify-between border-b pb-2">
                  <dt class="text-gray-500">Busca infrator cumpridos</dt>
                  <dd class="font-semibold text-gray-800">{{ operacao.resultado.mandados_busca_infrator_cumpridos }}</dd>
                </div>
                <div class="flex justify-between pb-2">
                  <dt class="text-gray-500">Prisões em flagrante</dt>
                  <dd class="font-semibold text-gray-800">{{ operacao.resultado.prisoes_flagrante }}</dd>
                </div>
              </dl>
            </div>

            <!-- Apreensões -->
            <div>
              <h4 class="text-sm font-semibold text-gray-600 mb-3 uppercase tracking-wide">Apreensões</h4>
              <dl class="space-y-2 text-sm">
                <div class="flex justify-between border-b pb-2">
                  <dt class="text-gray-500">Armas apreendidas</dt>
                  <dd class="font-semibold text-gray-800">{{ operacao.resultado.quantidade_armas_apreendidas }}</dd>
                </div>
                <div v-if="operacao.resultado.tipo_arma_apreendida?.length" class="border-b pb-2">
                  <dt class="text-gray-500 mb-1">Tipos de arma</dt>
                  <div class="flex flex-wrap gap-1">
                    <span
                      v-for="tipo in operacao.resultado.tipo_arma_apreendida"
                      :key="tipo"
                      class="inline-block px-2 py-0.5 rounded text-xs bg-red-100 text-red-800"
                    >
                      {{ tiposArma[tipo] ?? tipo }}
                    </span>
                  </div>
                </div>
                <div v-if="operacao.resultado.entorpecente_apreendido?.length" class="border-b pb-2">
                  <dt class="text-gray-500 mb-1">Entorpecentes</dt>
                  <div class="flex flex-wrap gap-1">
                    <span
                      v-for="tipo in operacao.resultado.entorpecente_apreendido"
                      :key="tipo"
                      class="inline-block px-2 py-0.5 rounded text-xs bg-purple-100 text-purple-800"
                    >
                      {{ tiposEntorpecente[tipo] ?? tipo }}
                    </span>
                  </div>
                </div>
                <div class="flex justify-between border-b pb-2">
                  <dt class="text-gray-500">Valores em dinheiro</dt>
                  <dd class="font-semibold text-gray-800">{{ formatarDinheiro(operacao.resultado.valores_dinheiro) }}</dd>
                </div>
                <div v-if="operacao.resultado.veiculos_apreendidos" class="flex justify-between border-b pb-2">
                  <dt class="text-gray-500">Veículos</dt>
                  <dd class="font-semibold text-gray-800 text-right max-w-xs">{{ operacao.resultado.veiculos_apreendidos }}</dd>
                </div>
                <div v-if="operacao.resultado.demais_objetos_apreendidos" class="flex justify-between pb-2">
                  <dt class="text-gray-500">Demais objetos</dt>
                  <dd class="font-semibold text-gray-800 text-right max-w-xs">{{ operacao.resultado.demais_objetos_apreendidos }}</dd>
                </div>
              </dl>
            </div>

            <!-- Outras informações -->
            <div v-if="operacao.resultado.outras_informacoes" class="lg:col-span-2">
              <h4 class="text-sm font-semibold text-gray-600 mb-2 uppercase tracking-wide">Outras Informações</h4>
              <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ operacao.resultado.outras_informacoes }}</p>
              </div>
            </div>

            <!-- Responsável pelo resultado -->
            <div class="lg:col-span-2 border-t pt-4 mt-2">
              <dl class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                <div>
                  <dt class="text-gray-500 font-medium text-xs uppercase">Autoridade (resultado)</dt>
                  <dd class="text-gray-800 mt-1">
                    {{ operacao.resultado.autoridade_responsavel_nome ?? '-' }}
                    <span v-if="operacao.resultado.autoridade_responsavel_matricula" class="block text-xs text-gray-400">
                      Mat. {{ operacao.resultado.autoridade_responsavel_matricula }}
                    </span>
                  </dd>
                </div>
                <div>
                  <dt class="text-gray-500 font-medium text-xs uppercase">Policial (resultado)</dt>
                  <dd class="text-gray-800 mt-1">
                    {{ operacao.resultado.policial_responsavel_nome ?? '-' }}
                    <span v-if="operacao.resultado.policial_responsavel_matricula" class="block text-xs text-gray-400">
                      Mat. {{ operacao.resultado.policial_responsavel_matricula }}
                    </span>
                  </dd>
                </div>
                <div>
                  <dt class="text-gray-500 font-medium text-xs uppercase">Cadastrado em</dt>
                  <dd class="text-gray-800 mt-1">{{ formatarDataHora(operacao.resultado.created_at) }}</dd>
                </div>
              </dl>
            </div>
          </div>
        </CardBox>
      </div>

      <!-- Datas de cadastro -->
      <div class="mt-4 text-xs text-gray-400 text-right">
        Cadastrada em {{ formatarDataHora(operacao.created_at) }}
        <span v-if="operacao.updated_at !== operacao.created_at">
          · Atualizada em {{ formatarDataHora(operacao.updated_at) }}
        </span>
      </div>

    </SectionMain>
  </LayoutAuthenticated>
</template>
