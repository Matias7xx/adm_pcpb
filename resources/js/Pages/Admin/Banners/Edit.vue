<script setup>
import { ref } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import {
  mdiArrowLeftBoldOutline,
  mdiContentSave,
  mdiImageMultiple,
  mdiAlertBoxOutline,
  mdiUpload,
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
  banner: {
    type: Object,
    required: true,
  },
});

const form = useForm({
  titulo: props.banner.titulo,
  descricao: props.banner.descricao || '',
  imagem: null,
  link: props.banner.link || '',
  nova_aba: props.banner.nova_aba,
  ordem: props.banner.ordem,
  ativo: props.banner.ativo,
  data_inicio: props.banner.data_inicio || '',
  data_fim: props.banner.data_fim || '',
  remover_imagem: false,
  _method: 'PUT', // Method spoofing para Laravel
});

const imagemPreview = ref(props.banner.imagem);
const fileInputRef = ref(null);

const handleImageSelect = event => {
  const file = event.target.files[0];
  if (file && validateImage(file)) {
    form.imagem = file;
    form.remover_imagem = false;

    const reader = new FileReader();
    reader.onload = e => {
      imagemPreview.value = e.target.result;
    };
    reader.readAsDataURL(file);
  }
};

const validateImage = file => {
  const validTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
  if (!validTypes.includes(file.type)) {
    alert('Formato de imagem inválido. Use JPEG, PNG, GIF ou WebP.');
    return false;
  }

  const maxSize = 5 * 1024 * 1024; // 5MB
  if (file.size > maxSize) {
    alert('A imagem não pode ter mais de 5MB.');
    return false;
  }

  return true;
};

const removeImage = () => {
  form.imagem = null;
  form.remover_imagem = true;
  imagemPreview.value = null;
  if (fileInputRef.value) {
    fileInputRef.value.value = '';
  }
};

const openFileDialog = () => {
  fileInputRef.value?.click();
};

const submit = () => {
  form.post(route('admin.banners.update', props.banner.id), {
    preserveScroll: true,
  });
};
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Editar Banner" />
    <SectionMain>
      <SectionTitleLineWithButton
        :icon="mdiImageMultiple"
        title="Editar Banner"
        main
      >
        <BaseButton
          :route-name="route('admin.banners.index')"
          :icon="mdiArrowLeftBoldOutline"
          label="Voltar"
          color="white"
          rounded-full
          small
        />
      </SectionTitleLineWithButton>

      <NotificationBar v-if="form.hasErrors" color="danger" :icon="mdiAlertBoxOutline">
        Por favor, corrija os erros no formulário
      </NotificationBar>

      <!-- Preview do Banner Atual -->
      <CardBox class="mb-6">
        <div class="mb-4">
          <h3 class="text-lg font-semibold mb-3 text-gray-900 dark:text-white">
            Preview do Banner Atual
          </h3>
          <div class="max-w-4xl">
            <img
              v-if="banner.imagem"
              :src="banner.imagem"
              :alt="banner.titulo"
              class="w-full h-auto rounded-lg shadow-lg"
            />
          </div>
        </div>
      </CardBox>

      <CardBox is-form @submit.prevent="submit">
        <FormField
          label="Título do Banner"
          help="Nome identificador do banner"
          required
        >
          <FormControl
            v-model="form.titulo"
            type="text"
            placeholder="Ex: Campanha de Verão 2025"
            :error="form.errors.titulo"
            required
          />
        </FormField>

        <FormField
          label="Descrição"
          help="Descrição opcional que aparece sobre o banner"
        >
          <FormControl
            v-model="form.descricao"
            type="textarea"
            placeholder="Descrição curta do banner..."
            :error="form.errors.descricao"
          />
        </FormField>

        <FormField
          label="Imagem do Banner"
          :required="!imagemPreview"
          help="Formato: JPEG, PNG, GIF ou WebP. Tamanho máximo: 5MB. Dimensões recomendadas: 1900x350px"
        >
          <div class="space-y-4">
            <!-- Preview -->
            <div v-if="imagemPreview" class="relative">
              <img
                :src="imagemPreview"
                alt="Preview"
                class="w-full max-h-64 object-cover rounded-lg border border-gray-300 dark:border-gray-600"
              />
              <button
                type="button"
                @click="removeImage"
                class="absolute top-2 right-2 bg-red-500 text-white p-2 rounded-full hover:bg-red-600 transition shadow-lg"
                title="Remover imagem"
              >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>

            <!-- Botão para adicionar/alterar -->
            <div>
              <input
                ref="fileInputRef"
                type="file"
                accept="image/*"
                @change="handleImageSelect"
                class="hidden"
              />
              <button
                type="button"
                @click="openFileDialog"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
              >
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                  <path :d="mdiUpload" />
                </svg>
                {{ imagemPreview ? 'Alterar Imagem' : 'Selecionar Imagem' }}
              </button>
            </div>

            <p v-if="form.errors.imagem" class="text-sm text-red-600">
              {{ form.errors.imagem }}
            </p>
          </div>
        </FormField>

        <FormField
          label="Link (Opcional)"
          help="URL para onde o banner deve redirecionar ao ser clicado"
        >
          <FormControl
            v-model="form.link"
            type="url"
            placeholder="https://exemplo.com"
            :error="form.errors.link"
          />
        </FormField>

        <FormField v-if="form.link" label="Comportamento do Link">
          <label class="flex items-center space-x-2 cursor-pointer">
            <input
              v-model="form.nova_aba"
              type="checkbox"
              class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
            />
            <span class="text-gray-700 dark:text-gray-300">
              Abrir link em nova aba
            </span>
          </label>
        </FormField>

        <FormField
          label="Ordem de Exibição"
          help="Número menor aparece primeiro (0 = primeiro)"
        >
          <FormControl
            v-model.number="form.ordem"
            type="number"
            :min="0"
            :error="form.errors.ordem"
          />
        </FormField>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <FormField
            label="Data de Início (Opcional)"
            help="Quando o banner começa a aparecer"
          >
            <FormControl
              v-model="form.data_inicio"
              type="date"
              :error="form.errors.data_inicio"
            />
          </FormField>

          <FormField
            label="Data de Fim (Opcional)"
            help="Quando o banner para de aparecer"
          >
            <FormControl
              v-model="form.data_fim"
              type="date"
              :error="form.errors.data_fim"
            />
          </FormField>
        </div>

        <FormField label="Status">
          <label class="flex items-center space-x-2 cursor-pointer">
            <input
              v-model="form.ativo"
              type="checkbox"
              class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
            />
            <span class="text-gray-700 dark:text-gray-300">
              Banner ativo (será exibido imediatamente)
            </span>
          </label>
        </FormField>

        <template #footer>
          <BaseButtons>
            <BaseButton
              color="info"
              label="Atualizar Banner"
              :icon="mdiContentSave"
              :disabled="form.processing"
              @click="submit"
            />
            <BaseButton
              :route-name="route('admin.banners.index')"
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
