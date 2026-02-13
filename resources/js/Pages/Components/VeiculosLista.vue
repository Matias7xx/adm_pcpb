<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const veiculos = ref([]);
const loading = ref(true);
const error = ref(null);

onMounted(async () => {
  await carregarVeiculos();
});

const carregarVeiculos = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/api/veiculos');
    veiculos.value = response.data;
  } catch (err) {
    console.error('Erro ao carregar veículos:', err);
    error.value = 'Não foi possível carregar os documentos.';
  } finally {
    loading.value = false;
  }
};

const getStatusBadge = veiculo => {
  if (veiculo.status_display && veiculo.status_display.type) {
    const colorMap = {
      inativo: 'bg-slate-100 border-slate-300 text-slate-700',
      leilao: 'bg-blue-50 border-blue-300 text-blue-700',
      critico: 'bg-red-50 border-red-300 text-red-700',
      proximo: 'bg-yellow-50 border-yellow-300 text-yellow-700',
      disponivel: 'bg-green-50 border-green-300 text-green-700',
    };

    return {
      color:
        colorMap[veiculo.status_display.type] ||
        'bg-slate-100 border-slate-300 text-slate-600',
      text: veiculo.status_display.text,
    };
  }
};
</script>

<template>
  <section class="veiculos-section">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12 lg:py-16">
      <!-- Header -->
      <div class="mb-8 sm:mb-10">
        <h1 class="section-title">
          Publicação de Relação de Veículos Apreendidos
        </h1>
        <p class="section-subtitle">
          Consulte os documentos oficiais contendo a relação de veículos
          apreendidos. Após o prazo legal, os veículos poderão ser destinados a
          leilão público.
        </p>
      </div>

      <!-- Loading State (Skeleton) -->
      <div v-if="loading" class="loading-container">
        <div class="skeleton-wrapper">
          <div class="skeleton-item" v-for="i in 3" :key="i">
            <div class="skeleton-line w-3/4"></div>
            <div class="skeleton-line w-1/2"></div>
            <div class="skeleton-line w-full"></div>
          </div>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="error-container" role="alert">
        <svg
          class="error-icon"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
          stroke-width="1.5"
          aria-hidden="true"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"
          />
        </svg>
        <p class="error-text">{{ error }}</p>
        <button
          @click="carregarVeiculos"
          class="btn-retry"
          aria-label="Tentar carregar novamente"
        >
          Tentar Novamente
        </button>
      </div>

      <!-- Empty State -->
      <div v-else-if="veiculos.length === 0" class="empty-container">
        <svg
          class="empty-icon"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
          stroke-width="1.5"
          aria-hidden="true"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"
          />
        </svg>
        <p class="empty-text">Nenhum documento disponível no momento</p>
        <p class="empty-subtext">Novos documentos serão publicados em breve</p>
      </div>

      <!-- Lista de Veículos -->
      <div v-else class="lista-container">
        <!-- Header da Lista (Desktop) -->
        <div class="lista-header hidden lg:grid" role="row">
          <div class="header-cell" role="columnheader">Documento</div>
          <div class="header-cell" role="columnheader">Publicação</div>
          <div class="header-cell" role="columnheader">Validade</div>
          <div class="header-cell" role="columnheader">Tamanho</div>
          <div class="header-cell" role="columnheader">Status</div>
          <div class="header-cell" role="columnheader">Ações</div>
        </div>

        <!-- Itens da Lista -->
        <div class="lista-body" role="rowgroup">
          <article
            v-for="veiculo in veiculos"
            :key="veiculo.id"
            class="lista-item"
            role="row"
          >
            <!-- Mobile: Card Compacto -->
            <div class="mobile-card lg:hidden">
              <!-- Header Mobile -->
              <div class="mobile-header">
                <div class="flex items-center gap-3 min-w-0 flex-1">
                  <!-- Título e Status -->
                  <div class="min-w-0 flex-1">
                    <h3 class="mobile-title">{{ veiculo.titulo }}</h3>
                    <div
                      :class="[
                        'status-badge',
                        'border',
                        getStatusBadge(veiculo).color,
                      ]"
                      role="status"
                      :aria-label="`Status: ${getStatusBadge(veiculo).text}`"
                    >
                      <span>{{ getStatusBadge(veiculo).text }}</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Info Mobile -->
              <div class="mobile-info">
                <div class="info-row">
                  <span class="info-label">Publicação:</span>
                  <span class="info-value">{{ veiculo.data_publicacao }}</span>
                </div>
                <div class="info-row">
                  <span class="info-label">Validade:</span>
                  <span class="info-value">{{ veiculo.data_expiracao }}</span>
                </div>
                <div class="info-row">
                  <span class="info-label">Tamanho:</span>
                  <span class="info-value">{{
                    veiculo.tamanho_formatado
                  }}</span>
                </div>
              </div>

              <!-- Ações Mobile -->
              <div class="mobile-actions">
                <a
                  v-if="veiculo.tipo_arquivo === 'pdf'"
                  :href="veiculo.url_preview"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="btn btn-secondary"
                  :aria-label="`Visualizar documento ${veiculo.titulo}`"
                >
                  <svg
                    class="btn-icon"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                    aria-hidden="true"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"
                    />
                  </svg>
                  Visualizar
                </a>

                <a
                  :href="veiculo.url_download"
                  download
                  class="btn btn-primary"
                  :aria-label="`Baixar documento ${veiculo.titulo}`"
                >
                  <svg
                    class="btn-icon"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                    aria-hidden="true"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"
                    />
                  </svg>
                  Download
                </a>
              </div>
            </div>

            <!-- Desktop: Lista -->
            <div class="desktop-row hidden lg:grid">
              <!-- Documento -->
              <div class="row-cell" role="cell">
                <span class="desktop-title" :title="veiculo.titulo">{{
                  veiculo.titulo
                }}</span>
              </div>

              <!-- Publicação -->
              <div class="row-cell" role="cell">
                <time class="desktop-text" :datetime="veiculo.data_publicacao">
                  {{ veiculo.data_publicacao }}
                </time>
              </div>

              <!-- Validade -->
              <div class="row-cell" role="cell">
                <time class="desktop-text" :datetime="veiculo.data_expiracao">
                  {{ veiculo.data_expiracao }}
                </time>
              </div>

              <!-- Tamanho -->
              <div class="row-cell" role="cell">
                <span class="desktop-text">{{
                  veiculo.tamanho_formatado
                }}</span>
              </div>

              <!-- Status -->
              <div class="row-cell" role="cell">
                <div
                  :class="[
                    'status-badge-desktop',
                    'border',
                    getStatusBadge(veiculo).color,
                  ]"
                  role="status"
                  :aria-label="`Status: ${getStatusBadge(veiculo).text}`"
                >
                  <span>{{ getStatusBadge(veiculo).text }}</span>
                </div>
              </div>

              <!-- Ações -->
              <div class="row-cell gap-2" role="cell">
                <a
                  v-if="veiculo.tipo_arquivo === 'pdf'"
                  :href="veiculo.url_preview"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="btn-icon-only"
                  :aria-label="`Visualizar documento ${veiculo.titulo}`"
                  title="Visualizar documento"
                >
                  <svg
                    class="w-5 h-5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                    aria-hidden="true"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"
                    />
                  </svg>
                </a>

                <a
                  :href="veiculo.url_download"
                  download
                  class="btn-icon-only btn-primary-icon"
                  :aria-label="`Baixar documento ${veiculo.titulo}`"
                  title="Baixar documento"
                >
                  <svg
                    class="w-5 h-5"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                    aria-hidden="true"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"
                    />
                  </svg>
                </a>
              </div>
            </div>
          </article>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
/* CONTAINER PRINCIPAL */
.veiculos-section {
  @apply min-h-screen bg-gradient-to-b from-slate-50 to-white;
}

/* TIPOGRAFIA */
.section-title {
  @apply text-2xl sm:text-3xl lg:text-4xl font-semibold tracking-tight text-slate-900 mb-2;
}

.section-subtitle {
  @apply text-sm sm:text-base text-slate-600 leading-relaxed max-w-3xl;
}

/* SKELETON LOADING */
.loading-container {
  @apply py-8;
}

.skeleton-wrapper {
  @apply space-y-4 bg-white rounded-xl border border-slate-200 p-6;
}

.skeleton-item {
  @apply space-y-3 animate-pulse;
}

.skeleton-line {
  @apply h-4 bg-slate-200 rounded;
}

/* ERROR STATE */
.error-container {
  @apply flex flex-col items-center justify-center py-16 bg-red-50 rounded-xl border border-red-200;
}

.error-icon {
  @apply h-12 w-12 text-red-400;
}

.error-text {
  @apply mt-3 text-sm text-red-700 font-medium;
}

.btn-retry {
  @apply mt-4 px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg
         hover:bg-red-700 transition-colors duration-150
         focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2;
}

/* EMPTY STATE */
.empty-container {
  @apply flex flex-col items-center justify-center py-20 bg-white rounded-xl border border-slate-200;
}

.empty-icon {
  @apply h-16 w-16 text-slate-300;
}

.empty-text {
  @apply mt-4 text-base font-medium text-slate-600;
}

.empty-subtext {
  @apply mt-1 text-sm text-slate-500;
}

/* LISTA CONTAINER */
.lista-container {
  @apply bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm;
}

/* Header da Lista (Desktop) */
.lista-header {
  @apply grid-cols-[minmax(200px,2fr)_130px_130px_100px_140px_110px] gap-4 px-6 py-3.5 
         bg-slate-50 border-b border-slate-200;
}

.header-cell {
  @apply text-xs font-semibold text-slate-600 uppercase tracking-wider;
}

/* Body da Lista */
.lista-body {
  @apply divide-y divide-slate-100;
}

/* LISTA ITEM */
.lista-item {
  @apply transition-all duration-200 hover:bg-slate-50/70;
}

/* MOBILE CARD */
.mobile-card {
  @apply p-5 space-y-3.5 mb-3 
         bg-white rounded-lg border border-slate-200
         shadow-sm hover:shadow-md hover:border-slate-300
         transition-all duration-200;
}

.mobile-card:last-child {
  @apply mb-0;
}

.mobile-header {
  @apply pb-3 border-b border-slate-100;
}

.mobile-title {
  @apply text-sm font-medium text-slate-900 line-clamp-2 leading-snug mb-2;
}

.status-badge {
  @apply inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md;
}

.mobile-info {
  @apply space-y-2 text-xs;
}

.info-row {
  @apply flex justify-between items-center;
}

.info-label {
  @apply text-slate-500 font-medium;
}

.info-value {
  @apply text-slate-900 font-semibold;
}

.mobile-actions {
  @apply flex gap-2 pt-1;
}

/* DESKTOP ROW */
.desktop-row {
  @apply grid-cols-[minmax(200px,2fr)_130px_130px_100px_140px_110px] gap-4 px-6 py-4 
         items-center;
}

.row-cell {
  @apply flex items-center;
}

.desktop-title {
  @apply text-sm font-medium text-slate-900 truncate hover:text-slate-600 transition-colors;
}

.desktop-text {
  @apply text-sm text-slate-600;
}

.status-badge-desktop {
  @apply inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-md
         whitespace-nowrap;
}

/* BOTÕES */
.btn {
  @apply flex-1 inline-flex items-center justify-center gap-2 
         px-4 py-2.5 rounded-lg text-sm font-medium
         transition-all duration-200;
}

.btn-primary {
  @apply bg-slate-900 text-white
         hover:bg-slate-800 hover:shadow-md
         active:scale-95;
}

.btn-secondary {
  @apply bg-white text-slate-700
         border border-slate-300
         hover:bg-slate-50 hover:border-slate-400 hover:shadow 
         active:scale-95;
}

.btn-icon {
  @apply w-4 h-4;
}

/* Botões apenas ícone (Desktop) */
.btn-icon-only {
  @apply p-2.5 rounded-lg text-slate-600
         hover:bg-slate-100 hover:text-slate-900 hover:scale-110
         transition-all duration-200;
}

.btn-primary-icon {
  @apply text-slate-900 hover:bg-slate-900 hover:text-white;
}

/* UTILITÁRIOS */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* ANIMAÇÕES */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.lista-item {
  animation: fadeIn 0.3s ease-out;
}

/* RESPONSIVIDADE */
@media (max-width: 1024px) {
  .mobile-card {
    @apply mx-2;
  }
}
</style>
