<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { mdiArrowLeftBoldOutline, mdiContentSave, mdiTruck } from '@mdi/js';
import LayoutAuthenticated from '@/Layouts/Admin/LayoutAuthenticated.vue';
import SectionMain from '@/Components/SectionMain.vue';
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue';
import CardBox from '@/Components/CardBox.vue';
import FormField from '@/Components/FormField.vue';
import FormControl from '@/Components/FormControl.vue';
import BaseButton from '@/Components/BaseButton.vue';
import BaseButtons from '@/Components/BaseButtons.vue';
import NotificationBar from '@/Components/NotificationBar.vue';

const form = useForm({
  titulo: '',
  descricao: '',
  arquivo: null,
  dias_exibicao: 15,
  data_publicacao: new Date().toISOString().split('T')[0],
  ativo: true,
});

const arquivoNome = ref('');

const handleFileChange = event => {
  const file = event.target.files[0];
  if (file) {
    form.arquivo = file;
    arquivoNome.value = file.name;
  }
};

const submit = () => {
  form.post(route('admin.veiculo.store'), {
    forceFormData: true,
    preserveScroll: true,
  });
};
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Adicionar Lista de Veículos" />

    <SectionMain>
      <SectionTitleLineWithButton
        :icon="mdiTruck"
        title="Adicionar Nova Lista de Veículos"
        main
      >
        <BaseButton
          :route-name="route('admin.veiculo.index')"
          :icon="mdiArrowLeftBoldOutline"
          label="Voltar"
          color="white"
          rounded-full
          small
        />
      </SectionTitleLineWithButton>

      <NotificationBar v-if="form.hasErrors" color="danger" :icon="mdiTruck">
        Por favor, corrija os erros no formulário
      </NotificationBar>

      <!-- Informação sobre o documento -->
      <!-- <CardBox class="mb-6">
        <div
          class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4"
        >
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <svg
                class="h-5 w-5 text-blue-600 dark:text-blue-400"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                  clip-rule="evenodd"
                />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                Sobre o sistema de expiração
              </h3>
              <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                <p>
                  O documento ficará visível ao público pelo período definido em
                  "Dias de Exibição".
                </p>
                <p class="mt-1">
                  Após esse período, o documento não será mais exibido
                  automaticamente.
                </p>
              </div>
            </div>
          </div>
        </div>
      </CardBox> -->

      <CardBox is-form @submit.prevent="submit">
        <FormField
          label="Título da Lista"
          help="Ex: Lista de Veículos Apreendidos - Janeiro/2025"
          required
        >
          <FormControl
            v-model="form.titulo"
            type="text"
            placeholder="Digite o título do documento"
            :error="form.errors.titulo"
            required
          />
        </FormField>

        <FormField
          label="Descrição"
          help="Informações adicionais sobre a lista (opcional)"
        >
          <FormControl
            v-model="form.descricao"
            type="textarea"
            placeholder="Ex: Lista contendo veículos apreendidos que irão a leilão"
            :error="form.errors.descricao"
          />
        </FormField>

        <FormField
          label="Arquivo (PDF ou Excel)"
          help="Tamanho máximo: 10MB. Formatos aceitos: PDF, XLSX, XLS"
          required
        >
          <div class="flex flex-col space-y-2">
            <label
              class="flex items-center justify-center w-full h-32 px-4 transition bg-white border-2 border-gray-300 border-dashed rounded-md appearance-none cursor-pointer hover:border-gray-400 focus:outline-none dark:bg-slate-800 dark:border-gray-600 dark:hover:border-gray-500"
            >
              <span class="flex items-center space-x-2">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="w-6 h-6 text-gray-600"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                  stroke-width="2"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
                  />
                </svg>
                <span class="font-medium text-gray-600">
                  {{
                    arquivoNome || 'Clique para selecionar ou arraste o arquivo'
                  }}
                </span>
              </span>
              <input
                type="file"
                accept=".pdf,.xlsx,.xls"
                class="hidden"
                @change="handleFileChange"
                required
              />
            </label>
            <p v-if="form.errors.arquivo" class="text-red-600 text-sm">
              {{ form.errors.arquivo }}
            </p>
          </div>
        </FormField>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <FormField
            label="Data de Publicação"
            help="Data em que o documento será considerado publicado"
            required
          >
            <FormControl
              v-model="form.data_publicacao"
              type="date"
              :error="form.errors.data_publicacao"
              required
            />
          </FormField>

          <FormField
            label="Dias de Exibição"
            help="Por quantos dias ficará visível (geralmente 15 dias)"
            required
          >
            <FormControl
              v-model="form.dias_exibicao"
              type="number"
              min="1"
              max="365"
              :error="form.errors.dias_exibicao"
              required
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
              Documento ativo (visível ao público)
            </span>
          </label>
        </FormField>

        <template #footer>
          <BaseButtons>
            <BaseButton
              color="info"
              label="Salvar Lista"
              :icon="mdiContentSave"
              :disabled="form.processing"
              @click="submit"
            />
            <BaseButton
              :route-name="route('admin.veiculo.index')"
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
