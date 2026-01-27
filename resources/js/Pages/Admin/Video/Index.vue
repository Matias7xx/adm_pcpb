<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
  mdiVideoPlus,
  mdiSquareEditOutline,
  mdiTrashCan,
  mdiEye,
  mdiEyeOff,
  mdiYoutube,
} from '@mdi/js';
import LayoutAuthenticated from '@/Layouts/Admin/LayoutAuthenticated.vue';
import SectionMain from '@/Components/SectionMain.vue';
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue';
import BaseButton from '@/Components/BaseButton.vue';
import CardBox from '@/Components/CardBox.vue';
import BaseButtons from '@/Components/BaseButtons.vue';
import Pagination from '@/Components/Admin/Pagination.vue';
import Sort from '@/Components/Admin/Sort.vue';
import NotificationBar from '@/Components/NotificationBar.vue';

const props = defineProps({
  videos: {
    type: Object,
    required: true,
  },
  filters: Object,
  can: Object,
});

const form = useForm({
  search: props.filters?.search || '',
});

const formDelete = useForm({});

const submit = () => {
  form.get(route('admin.video.index'));
};

const destroy = id => {
  if (confirm('Tem certeza que deseja excluir este vídeo?')) {
    formDelete.delete(route('admin.video.destroy', id));
  }
};

const toggleAtivo = id => {
  formDelete.patch(route('admin.video.toggle-ativo', id));
};

// Formatar visualizações
const formatVisualizacoes = count => {
  if (count >= 1000000) {
    return (count / 1000000).toFixed(1) + 'M';
  }
  if (count >= 1000) {
    return (count / 1000).toFixed(1) + 'K';
  }
  return count;
};
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Gerenciar Vídeos" />
    <SectionMain>
      <SectionTitleLineWithButton
        :icon="mdiYoutube"
        title="Vídeos Institucionais"
        main
      >
        <BaseButton
          v-if="can.create"
          :route-name="route('admin.video.create')"
          :icon="mdiVideoPlus"
          label="Adicionar Vídeo"
          color="info"
          rounded-full
          small
        />
      </SectionTitleLineWithButton>

      <NotificationBar
        v-if="$page.props.flash.message"
        color="success"
        :icon="mdiYoutube"
      >
        {{ $page.props.flash.message }}
      </NotificationBar>

      <!-- Busca -->
      <CardBox class="mb-6" is-form @submit.prevent="submit">
        <form>
          <div class="py-2">
            <div class="flex gap-4">
              <input
                v-model="form.search"
                type="text"
                class="flex-1 px-3 py-2 max-w-full focus:ring focus:outline-none border-gray-700 rounded w-full dark:placeholder-gray-400 bg-white dark:bg-slate-800 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-gray-900"
                placeholder="Buscar por título ou descrição"
              />
              <BaseButton
                label="Buscar"
                type="submit"
                color="info"
                class="inline-flex items-center px-4 py-2"
              />
            </div>
          </div>
        </form>
      </CardBox>

      <!-- Tabela de Vídeos -->
      <CardBox class="mb-6" has-table>
        <table>
          <thead>
            <tr>
              <th><Sort label="Título" attribute="titulo" /></th>
              <th>Preview</th>
              <th><Sort label="Ordem" attribute="ordem" /></th>
              <th>Status</th>
              <th>Visualizações</th>
              <th v-if="can.edit || can.delete">Ações</th>
            </tr>
          </thead>

          <tbody>
            <tr v-if="videos.data.length === 0">
              <td colspan="6" class="text-center py-8 text-gray-500">
                Nenhum vídeo cadastrado
              </td>
            </tr>
            <tr v-for="video in videos.data" :key="video.id">
              <td data-label="Título">
                <div class="flex items-center space-x-3">
                  <div>
                    <div class="font-medium text-gray-900 dark:text-white">
                      {{ video.titulo }}
                    </div>
                    <div
                      v-if="video.descricao"
                      class="text-sm text-gray-500 line-clamp-1"
                    >
                      {{ video.descricao }}
                    </div>
                  </div>
                </div>
              </td>
              <td data-label="Preview">
                <div class="flex items-center">
                  <img
                    v-if="video.thumbnail_url"
                    :src="video.thumbnail_url"
                    :alt="video.titulo"
                    class="w-24 h-14 object-cover rounded shadow"
                    loading="lazy"
                  />
                  <div
                    v-else
                    class="w-24 h-14 bg-gray-200 rounded flex items-center justify-center"
                  >
                    <svg
                      class="w-8 h-8 text-gray-400"
                      fill="currentColor"
                      viewBox="0 0 20 20"
                    >
                      <path
                        d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"
                      />
                    </svg>
                  </div>
                </div>
              </td>
              <td data-label="Ordem">
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800"
                >
                  {{ video.ordem }}
                </span>
              </td>
              <td data-label="Status">
                <button
                  @click="toggleAtivo(video.id)"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium transition-colors"
                  :class="
                    video.ativo
                      ? 'bg-green-100 text-green-800 hover:bg-green-200'
                      : 'bg-red-100 text-red-800 hover:bg-red-200'
                  "
                >
                  <svg
                    class="w-3 h-3 mr-1"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path v-if="video.ativo" :d="mdiEye" />
                    <path v-else :d="mdiEyeOff" />
                  </svg>
                  {{ video.ativo ? 'Ativo' : 'Inativo' }}
                </button>
              </td>
              <td data-label="Visualizações">
                <span class="text-gray-600">
                  {{ formatVisualizacoes(video.visualizacoes) }}
                </span>
              </td>
              <td
                v-if="can.edit || can.delete"
                class="before:hidden lg:w-1 whitespace-nowrap"
              >
                <BaseButtons type="justify-start lg:justify-end" no-wrap>
                  <BaseButton
                    v-if="can.edit"
                    :route-name="route('admin.video.edit', video.id)"
                    color="info"
                    :icon="mdiSquareEditOutline"
                    small
                  />
                  <BaseButton
                    v-if="can.delete"
                    color="danger"
                    :icon="mdiTrashCan"
                    small
                    @click="destroy(video.id)"
                  />
                </BaseButtons>
              </td>
            </tr>
          </tbody>
        </table>
        <div class="py-4">
          <Pagination :data="videos" />
        </div>
      </CardBox>
    </SectionMain>
  </LayoutAuthenticated>
</template>

<style scoped>
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
