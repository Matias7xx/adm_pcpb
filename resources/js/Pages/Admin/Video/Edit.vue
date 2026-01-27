<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import {
  mdiArrowLeftBoldOutline,
  mdiContentSave,
  mdiYoutube,
  mdiAlertBoxOutline,
} from '@mdi/js';
import LayoutAuthenticated from '@/Layouts/Admin/LayoutAuthenticated.vue';
import SectionMain from '@/Components/SectionMain.vue';
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue';
import CardBox from '@/Components/CardBox.vue';
import FormField from '@/Components/FormField.vue';
import FormControl from '@/Components/FormControl.vue';
import BaseButton from '@/Components/BaseButton.vue';
import BaseButtons from '@/Components/BaseButtons.vue';
import NotificationBar from '@/Components/NotificationBar.vue';

const props = defineProps({
  video: {
    type: Object,
    required: true,
  },
});

const form = useForm({
  titulo: props.video.titulo,
  youtube_url: props.video.youtube_url,
  descricao: props.video.descricao || '',
  ordem: props.video.ordem,
  ativo: props.video.ativo,
});

const submit = () => {
  form.put(route('admin.video.update', props.video.id), {
    preserveScroll: true,
  });
};
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Editar Vídeo" />
    <SectionMain>
      <SectionTitleLineWithButton :icon="mdiYoutube" title="Editar Vídeo" main>
        <BaseButton
          :route-name="route('admin.video.index')"
          :icon="mdiArrowLeftBoldOutline"
          label="Voltar"
          color="white"
          rounded-full
          small
        />
      </SectionTitleLineWithButton>

      <NotificationBar v-if="form.hasErrors" color="danger" :icon="mdiYoutube">
        Por favor, corrija os erros no formulário
      </NotificationBar>

      <!-- Preview do Vídeo -->
      <CardBox class="mb-6">
        <div class="mb-4">
          <h3 class="text-lg font-semibold mb-3">Preview do Vídeo Atual</h3>
          <div class="aspect-video max-w-2xl">
            <iframe
              v-if="video.embed_url"
              :src="video.embed_url"
              :title="video.titulo"
              frameborder="0"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
              allowfullscreen
              class="w-full h-full rounded-lg shadow-lg"
            ></iframe>
          </div>
        </div>
      </CardBox>

      <CardBox is-form @submit.prevent="submit">
        <FormField
          label="Título do Vídeo"
          help="Título que será exibido no card do vídeo"
          required
        >
          <FormControl
            v-model="form.titulo"
            type="text"
            placeholder="Ex: Curso de Formação da Polícia Civil"
            :error="form.errors.titulo"
            required
          />
        </FormField>

        <FormField
          label="URL do YouTube"
          help="Cole aqui o link completo do vídeo no YouTube"
          required
        >
          <FormControl
            v-model="form.youtube_url"
            type="url"
            placeholder="https://www.youtube.com/watch?v=..."
            :error="form.errors.youtube_url"
            required
          >
            <template #prefix>
              <svg
                class="w-5 h-5 text-red-600"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  d="M10 0C4.477 0 0 4.477 0 10s4.477 10 10 10 10-4.477 10-10S15.523 0 10 0zm3.5 10.833l-6-3.5v7l6-3.5z"
                />
              </svg>
            </template>
          </FormControl>
          <p class="text-sm text-gray-500 mt-1">
            Exemplos válidos: youtube.com/watch?v=..., youtu.be/...,
            youtube.com/embed/...
          </p>
        </FormField>

        <FormField label="Descrição" help="Descrição breve do vídeo (opcional)">
          <FormControl
            v-model="form.descricao"
            type="textarea"
            placeholder="Descrição do vídeo..."
            :error="form.errors.descricao"
          />
        </FormField>

        <FormField
          label="Ordem de Exibição"
          help="Ordem em que o vídeo aparecerá (0 = primeiro)"
        >
          <FormControl
            v-model="form.ordem"
            type="number"
            :min="0"
            :error="form.errors.ordem"
          />
        </FormField>

        <FormField label="Status">
          <label class="flex items-center space-x-2 cursor-pointer">
            <input
              v-model="form.ativo"
              type="checkbox"
              class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
            />
            <span class="text-gray-700 dark:text-gray-300"
              >Vídeo ativo (visível no site)</span
            >
          </label>
        </FormField>

        <template #footer>
          <BaseButtons>
            <BaseButton
              color="info"
              label="Atualizar Vídeo"
              :icon="mdiContentSave"
              :disabled="form.processing"
              @click="submit"
            />
            <BaseButton
              :route-name="route('admin.video.index')"
              color="white"
              outline
              label="Cancelar"
            />
          </BaseButtons>
        </template>
      </CardBox>
    </SectionMain>
  </LayoutAuthenticated>
</template>
