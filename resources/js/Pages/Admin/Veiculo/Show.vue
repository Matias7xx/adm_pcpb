<script setup>
import { Head } from '@inertiajs/vue3'
import { computed } from 'vue'
import LayoutAuthenticated from '@/Layouts/Admin/LayoutAuthenticated.vue';
import SectionMain from '@/Components/SectionMain.vue'
import CardBox from '@/Components/CardBox.vue'
import BaseButton from '@/Components/BaseButton.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import { mdiArrowLeft, mdiPencil, mdiDownload, mdiOpenInNew } from '@mdi/js'

const props = defineProps({
  veiculo: {
    type: Object,
    required: true,
  },
})

const statusBadge = computed(() => {
  if (!props.veiculo.ativo) {
    return { color: 'bg-gray-500', text: 'Inativo' }
  }
  if (props.veiculo.expirado) {
    return { color: 'bg-red-500', text: 'Expirado' }
  }
  if (props.veiculo.dias_restantes <= 3) {
    return { color: 'bg-orange-500', text: `${props.veiculo.dias_restantes} dias restantes` }
  }
  if (props.veiculo.dias_restantes <= 7) {
    return { color: 'bg-yellow-500', text: `${props.veiculo.dias_restantes} dias restantes` }
  }
  return { color: 'bg-green-500', text: 'Ativo' }
})

const tipoIcon = computed(() => {
  return props.veiculo.tipo_arquivo === 'pdf' ? '📄' : '📊'
})
</script>

<template>
  <LayoutAuthenticated>
    <Head :title="`Veículo: ${veiculo.titulo}`" />

    <SectionMain>
      <SectionTitleLineWithButton
        :icon="mdiArrowLeft"
        :title="veiculo.titulo"
        main
      >
        <BaseButtons>
          <BaseButton
            :route-name="route('admin.veiculo.edit', veiculo.id)"
            :icon="mdiPencil"
            label="Editar"
            color="warning"
            rounded-full
            small
          />
          <BaseButton
            :route-name="route('admin.veiculo.index')"
            :icon="mdiArrowLeft"
            label="Voltar"
            color="contrast"
            rounded-full
            small
          />
        </BaseButtons>
      </SectionTitleLineWithButton>

      <!-- Status e Informações Principais -->
      <CardBox class="mb-6">
        <div class="flex items-start justify-between mb-4">
          <div class="flex items-center space-x-3">
            <div class="text-4xl">{{ tipoIcon }}</div>
            <div>
              <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                {{ veiculo.titulo }}
              </h2>
              <span
                :class="[
                  'inline-block px-3 py-1 text-sm rounded-full text-white mt-2',
                  statusBadge.color
                ]"
              >
                {{ statusBadge.text }}
              </span>
            </div>
          </div>
        </div>

        <div v-if="veiculo.descricao" class="mb-6">
          <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-2">
            Descrição
          </h3>
          <p class="text-gray-600 dark:text-gray-400">{{ veiculo.descricao }}</p>
        </div>

        <!-- Grid de Informações -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <!-- Tipo de Arquivo -->
          <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-lg">
            <div class="flex items-center space-x-2 mb-2">
              <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
              </svg>
              <span class="text-sm font-medium text-blue-900 dark:text-blue-200">Tipo</span>
            </div>
            <p class="text-xl font-bold text-blue-600 dark:text-blue-400">
              {{ veiculo.tipo_arquivo.toUpperCase() }}
            </p>
          </div>

          <!-- Tamanho -->
          <div class="bg-purple-50 dark:bg-purple-900/20 p-4 rounded-lg">
            <div class="flex items-center space-x-2 mb-2">
              <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                <path d="M3 12v3c0 1.657 3.134 3 7 3s7-1.343 7-3v-3c0 1.657-3.134 3-7 3s-7-1.343-7-3z" />
                <path d="M3 7v3c0 1.657 3.134 3 7 3s7-1.343 7-3V7c0 1.657-3.134 3-7 3S3 8.657 3 7z" />
                <path d="M17 5c0 1.657-3.134 3-7 3S3 6.657 3 5s3.134-3 7-3 7 1.343 7 3z" />
              </svg>
              <span class="text-sm font-medium text-purple-900 dark:text-purple-200">Tamanho</span>
            </div>
            <p class="text-xl font-bold text-purple-600 dark:text-purple-400">
              {{ veiculo.tamanho_formatado }}
            </p>
          </div>

          <!-- Downloads -->
          <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
            <div class="flex items-center space-x-2 mb-2">
              <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
              <span class="text-sm font-medium text-green-900 dark:text-green-200">Downloads</span>
            </div>
            <p class="text-xl font-bold text-green-600 dark:text-green-400">
              {{ veiculo.downloads }}
            </p>
          </div>
        </div>
      </CardBox>

      <!-- Datas -->
      <CardBox class="mb-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
          Informações de Publicação
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Data de Publicação -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Data de Publicação
            </label>
            <div class="flex items-center space-x-2 text-gray-900 dark:text-white">
              <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
              </svg>
              <span class="font-medium">
                {{ new Date(veiculo.data_publicacao).toLocaleDateString('pt-BR') }}
              </span>
            </div>
          </div>

          <!-- Data de Expiração -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Data de Expiração
            </label>
            <div class="flex items-center space-x-2 text-gray-900 dark:text-white">
              <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
              </svg>
              <span class="font-medium">
                {{ new Date(veiculo.data_expiracao).toLocaleDateString('pt-BR') }}
              </span>
            </div>
          </div>

          <!-- Dias de Exibição -->
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
              Período de Exibição
            </label>
            <div class="flex items-center space-x-2 text-gray-900 dark:text-white">
              <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
              </svg>
              <span class="font-medium">{{ veiculo.dias_exibicao }} dias</span>
            </div>
          </div>
        </div>

        <!-- Alerta de Expiração -->
        <div
          v-if="!veiculo.expirado && veiculo.dias_restantes <= 7"
          class="mt-6 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4"
        >
          <div class="flex items-start">
            <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                Atenção: Documento próximo de expirar
              </h3>
              <p class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
                Este documento expirará em {{ veiculo.dias_restantes }} dia{{ veiculo.dias_restantes !== 1 ? 's' : '' }}.
              </p>
            </div>
          </div>
        </div>

        <!-- Alerta de Expirado -->
        <div
          v-if="veiculo.expirado"
          class="mt-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4"
        >
          <div class="flex items-start">
            <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                Documento Expirado
              </h3>
              <p class="mt-1 text-sm text-red-700 dark:text-red-300">
                Este documento não está mais visível ao público.
              </p>
            </div>
          </div>
        </div>
      </CardBox>

      <!-- Arquivo -->
      <CardBox>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
          Arquivo
        </h3>

        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
          <div class="flex items-center space-x-3">
          <div class="text-3xl">{{ tipoIcon }}</div>
          <div>
            <p class="font-medium text-gray-900 dark:text-white">{{ veiculo.arquivo }}</p>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ veiculo.tamanho_formatado }}</p>
          </div>
        </div>

        <BaseButtons mb-0> 
          <BaseButton v-if="veiculo.tipo_arquivo === 'pdf'"
            :href="veiculo.url_preview"
            target="_blank"
            rel="noopener noreferrer"
            :icon="mdiOpenInNew"
            label="Visualizar"
            color="info"
          />

          <BaseButton
            :href="route('veiculos.download', veiculo.id)"
            :icon="mdiDownload"
            label="Download"
            color="success"
            target="_blank"
          />
        </BaseButtons>
      </div>
      </CardBox>
    </SectionMain>
  </LayoutAuthenticated>
</template>