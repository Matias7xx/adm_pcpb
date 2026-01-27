<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {
  mdiImageMultiple,
  mdiPlus,
  mdiSquareEditOutline,
  mdiTrashCan,
  mdiEye,
  mdiEyeOff,
  mdiAlertBoxOutline,
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
  banners: {
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
  form.get(route('admin.banners.index'));
};

const destroy = id => {
  if (confirm('Tem certeza que deseja excluir este banner?')) {
    formDelete.delete(route('admin.banners.destroy', id));
  }
};

const toggleAtivo = id => {
  formDelete.patch(route('admin.banners.toggleAtivo', id));
};
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Gerenciar Banners" />
    <SectionMain>
      <SectionTitleLineWithButton
        :icon="mdiImageMultiple"
        title="Banners da Página Inicial"
        main
      >
        <BaseButton
          v-if="can.create"
          :route-name="route('admin.banners.create')"
          :icon="mdiPlus"
          label="Adicionar Banner"
          color="info"
          rounded-full
          small
        />
      </SectionTitleLineWithButton>

      <NotificationBar
        v-if="$page.props.flash.message"
        color="success"
        :icon="mdiAlertBoxOutline"
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
                class="flex-1 px-3 py-2 max-w-full focus:ring focus:outline-none border-gray-700 rounded w-full dark:placeholder-gray-400 bg-white dark:bg-slate-800 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 text-gray-900 dark:text-white"
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

      <!-- Tabela de Banners -->
      <CardBox class="mb-6" has-table>
        <table>
          <thead>
            <tr>
              <th><Sort label="Título" attribute="titulo" /></th>
              <th>Preview</th>
              <th><Sort label="Ordem" attribute="ordem" /></th>
              <th>Link</th>
              <th>Período</th>
              <th>Status</th>
              <th v-if="can.edit || can.delete">Ações</th>
            </tr>
          </thead>

          <tbody>
            <tr v-if="banners.data.length === 0">
              <td colspan="7" class="text-center py-8 text-gray-500 dark:text-gray-400">
                Nenhum banner cadastrado
              </td>
            </tr>
            <tr v-for="banner in banners.data" :key="banner.id">
              <td data-label="Título">
                <div class="flex items-center space-x-3">
                  <div>
                    <div class="font-medium text-gray-900 dark:text-white">
                      {{ banner.titulo }}
                    </div>
                    <div
                      v-if="banner.descricao"
                      class="text-sm text-gray-500 dark:text-gray-400 line-clamp-1"
                    >
                      {{ banner.descricao }}
                    </div>
                  </div>
                </div>
              </td>
              <td data-label="Preview">
                <div class="flex items-center">
                  <img
                    v-if="banner.imagem"
                    :src="banner.imagem"
                    :alt="banner.titulo"
                    class="w-32 h-20 object-cover rounded shadow"
                    loading="lazy"
                  />
                  <div
                    v-else
                    class="w-32 h-20 bg-gray-200 dark:bg-gray-700 rounded flex items-center justify-center"
                  >
                    <svg
                      class="w-8 h-8 text-gray-400"
                      fill="currentColor"
                      viewBox="0 0 20 20"
                    >
                      <path
                        d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z"
                      />
                    </svg>
                  </div>
                </div>
              </td>
              <td data-label="Ordem">
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300"
                >
                  {{ banner.ordem }}
                </span>
              </td>
              <td data-label="Link">
                <span
                  v-if="banner.link"
                  class="inline-flex items-center text-xs text-blue-600 dark:text-blue-400"
                >
                  <svg
                    class="w-3 h-3 mr-1"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"
                    />
                  </svg>
                  Com link
                </span>
                <span v-else class="text-gray-400 text-xs">Sem link</span>
              </td>
              <td data-label="Período">
                <div class="text-xs">
                  <div v-if="banner.data_inicio" class="text-gray-600 dark:text-gray-400">
                    {{ banner.data_inicio }}
                  </div>
                  <div v-if="banner.data_fim" class="text-gray-600 dark:text-gray-400">
                    até {{ banner.data_fim }}
                  </div>
                  <div
                    v-if="!banner.data_inicio && !banner.data_fim"
                    class="text-gray-400"
                  >
                    Sem período
                  </div>
                </div>
              </td>
              <td data-label="Status">
                <button
                  @click="toggleAtivo(banner.id)"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium transition-colors"
                  :class="
                    banner.ativo && banner.pode_ser_exibido
                      ? 'bg-green-100 text-green-800 hover:bg-green-200'
                      : banner.ativo && !banner.pode_ser_exibido
                        ? 'bg-orange-100 text-orange-800 hover:bg-orange-200'
                        : 'bg-red-100 text-red-800 hover:bg-red-200'
                  "
                >
                  <svg
                    class="w-3 h-3 mr-1"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                  >
                    <path v-if="banner.ativo" :d="mdiEye" />
                    <path v-else :d="mdiEyeOff" />
                  </svg>
                  {{
                    banner.ativo && banner.pode_ser_exibido
                      ? 'Ativo'
                      : banner.ativo && !banner.pode_ser_exibido
                        ? 'Fora do período'
                        : 'Inativo'
                  }}
                </button>
              </td>
              <td
                v-if="can.edit || can.delete"
                class="before:hidden lg:w-1 whitespace-nowrap"
              >
                <BaseButtons type="justify-start lg:justify-end" no-wrap>
                  <BaseButton
                    v-if="can.edit"
                    :route-name="route('admin.banners.edit', banner.id)"
                    color="info"
                    :icon="mdiSquareEditOutline"
                    small
                  />
                  <BaseButton
                    v-if="can.delete"
                    color="danger"
                    :icon="mdiTrashCan"
                    small
                    @click="destroy(banner.id)"
                  />
                </BaseButtons>
              </td>
            </tr>
          </tbody>
        </table>
        <div class="py-4">
          <Pagination :data="banners" />
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
