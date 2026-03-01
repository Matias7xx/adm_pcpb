<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { useToast } from '@/Composables/useToast';
import {
  mdiAccountKey,
  mdiArrowLeftBoldOutline,
  mdiPlus,
  mdiDownload,
  mdiDelete,
  mdiEye,
  mdiUpload,
  mdiClose,
  mdiCalendar,
  mdiSchool,
  mdiClockOutline,
  mdiMapMarker,
  mdiWeb,
} from '@mdi/js';
import LayoutAuthenticated from '@/Layouts/Admin/LayoutAuthenticated.vue';
import SectionMain from '@/Components/SectionMain.vue';
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue';
import CardBox from '@/Components/CardBox.vue';
import BaseButton from '@/Components/BaseButton.vue';
import BaseButtons from '@/Components/BaseButtons.vue';
import FormField from '@/Components/FormField.vue';

const props = defineProps({
  user: {
    type: Object,
    default: () => ({}),
  },
  roles: {
    type: Object,
    default: () => ({}),
  },
  userHasRoles: {
    type: Object,
    default: () => ({}),
  },
  cursos: {
    type: Array,
    default: () => [],
  },
});

// Toast para notificações
const { toast } = useToast();

// Funções de formatação
const formatDate = dateString => {
  if (!dateString) return '-';

  const date = new Date(dateString);
  return date.toLocaleDateString('pt-BR');
};

const formatCPF = cpf => {
  if (!cpf) return '-';

  const cleanCPF = cpf.replace(/\D/g, '');

  if (cleanCPF.length !== 11) return cpf;

  return cleanCPF.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
};

const formatPhone = phone => {
  if (!phone) return '-';

  const cleanPhone = phone.replace(/\D/g, '');

  if (cleanPhone.length === 11) {
    return cleanPhone.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
  } else if (cleanPhone.length === 10) {
    return cleanPhone.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
  }

  return phone;
};
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Detalhes Usuário" />
    <SectionMain>
      <SectionTitleLineWithButton
        :icon="mdiAccountKey"
        title="Detalhes do Usuário"
        main
      >
        <BaseButton
          :route-name="route('admin.user.index')"
          :icon="mdiArrowLeftBoldOutline"
          label="Voltar"
          color="white"
          rounded-full
          small
        />
      </SectionTitleLineWithButton>

      <!-- Informações do Usuário -->
      <CardBox class="mb-6">
        <table>
          <tbody>
            <tr>
              <td
                class="p-4 pl-8 text-slate-500 dark:text-slate-400 hidden lg:block"
              >
                Nome
              </td>
              <td data-label="Nome">
                {{ user.name }}
              </td>
            </tr>
            <tr>
              <td
                class="p-4 pl-8 text-slate-500 dark:text-slate-400 hidden lg:block"
              >
                E-mail
              </td>
              <td data-label="E-mail">
                {{ user.email }}
              </td>
            </tr>
            <tr>
              <td
                class="p-4 pl-8 text-slate-500 dark:text-slate-400 hidden lg:block"
              >
                Matrícula
              </td>
              <td data-label="Matrícula">
                {{ user.matricula }}
              </td>
            </tr>
            <tr>
              <td
                class="p-4 pl-8 text-slate-500 dark:text-slate-400 hidden lg:block"
              >
                CPF
              </td>
              <td data-label="CPF">
                {{ formatCPF(user.cpf) }}
              </td>
            </tr>
            <tr>
              <td
                class="p-4 pl-8 text-slate-500 dark:text-slate-400 hidden lg:block"
              >
                Cargo
              </td>
              <td data-label="Cargo">
                {{ user.cargo || '-' }}
              </td>
            </tr>
            <tr>
              <td
                class="p-4 pl-8 text-slate-500 dark:text-slate-400 hidden lg:block"
              >
                Órgão
              </td>
              <td data-label="Órgão">
                {{ user.orgao || '-' }}
              </td>
            </tr>
            <tr>
              <td
                class="p-4 pl-8 text-slate-500 dark:text-slate-400 hidden lg:block"
              >
                Lotação
              </td>
              <td data-label="Lotação">
                {{ user.lotacao || '-' }}
              </td>
            </tr>
            <tr>
              <td
                class="p-4 pl-8 text-slate-500 dark:text-slate-400 hidden lg:block"
              >
                Telefone
              </td>
              <td data-label="Telefone">
                {{ formatPhone(user.telefone) }}
              </td>
            </tr>
            <tr>
              <td
                class="p-4 pl-8 text-slate-500 dark:text-slate-400 hidden lg:block"
              >
                Data de Nascimento
              </td>
              <td data-label="Data de Nascimento">
                {{ formatDate(user.data_nascimento) }}
              </td>
            </tr>
            <tr>
              <td
                class="p-4 pl-8 text-slate-500 dark:text-slate-400 hidden lg:block"
              >
                Funções
              </td>
              <td data-label="Funções">
                <div class="flex flex-wrap gap-1">
                  <span
                    v-for="role in userHasRoles"
                    :key="role"
                    class="bg-blue-100 text-blue-800 text-xs font-medium mr-2 px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300"
                  >
                    {{ role }}
                  </span>
                </div>
              </td>
            </tr>
            <tr>
              <td
                class="p-4 pl-8 text-slate-500 dark:text-slate-400 hidden lg:block"
              >
                Criado em
              </td>
              <td data-label="Criado em">
                {{ formatDate(user.created_at) }}
              </td>
            </tr>
            <tr>
              <td
                class="p-4 pl-8 text-slate-500 dark:text-slate-400 hidden lg:block"
              >
                Atualizado em
              </td>
              <td data-label="Atualizado em">
                {{ formatDate(user.updated_at) }}
              </td>
            </tr>
          </tbody>
        </table>
      </CardBox>
    </SectionMain>
  </LayoutAuthenticated>
</template>

<style scoped>
.modal-overlay {
  backdrop-filter: blur(4px);
}

/* Animações para modais */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

/* Loading spinner */
.loading-spinner {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

/* Drag and drop visual feedback */
.drop-zone {
  transition: all 0.2s ease-in-out;
}

.drop-zone.drag-over {
  border-color: #f59e0b;
  background-color: rgba(245, 158, 11, 0.05);
  transform: scale(1.02);
}

/* Badge animations */
.badge {
  transition: all 0.2s ease-in-out;
}

.badge:hover {
  transform: scale(1.05);
}

/* Button hover improvements */
.btn-hover {
  transition: all 0.2s ease-in-out;
}

.btn-hover:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

/* Form field focus states */
.form-input:focus {
  transform: scale(1.01);
  transition: transform 0.2s ease-in-out;
}

/* Progress bar animation */
.progress-bar {
  background: linear-gradient(45deg, #f59e0b, #d97706);
  background-size: 200% 100%;
  animation: gradient-shift 2s ease-in-out infinite;
}

@keyframes gradient-shift {
  0% {
    background-position: 200% 0;
  }
  100% {
    background-position: -200% 0;
  }
}

/* Mobile responsiveness improvements */
@media (max-width: 768px) {
  .modal-container {
    margin: 1rem;
    max-height: calc(100vh - 2rem);
  }

  .action-buttons {
    flex-direction: column;
    gap: 0.5rem;
  }
}

/* Accessibility improvements */
.focus-visible {
  outline: 2px solid #f59e0b;
  outline-offset: 2px;
}

/* File upload area specific styling */
.upload-area {
  background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%);
  border: 2px dashed #d1d5db;
  transition: all 0.3s ease;
}

.upload-area:hover {
  border-color: #f59e0b;
  background: linear-gradient(135deg, #fef3e2 0%, #fef2e0 100%);
}

.upload-area.drag-active {
  border-color: #f59e0b;
  background: linear-gradient(135deg, #fef3e2 0%, #fef2e0 100%);
  transform: scale(1.02);
}

/* Success/Error message styling */
.message-success {
  background: linear-gradient(90deg, #dcfce7 0%, #bbf7d0 100%);
  border-left: 4px solid #16a34a;
}

.message-error {
  background: linear-gradient(90deg, #fef2f2 0%, #fecaca 100%);
  border-left: 4px solid #dc2626;
}
</style>
