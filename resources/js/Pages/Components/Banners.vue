<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const banners    = ref([]);
const loading    = ref(true);
const imageLoaded = ref({});

const fetchBanners = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/banners?tipo=inferior');
    banners.value  = response.data;
  } catch (error) {
    console.error('Erro ao carregar banners inferiores:', error);
    banners.value = [];
  } finally {
    loading.value = false;
  }
};

const handleImageLoad = id => {
  imageLoaded.value[id] = true;
};

/**
 * Classes do grid conforme a quantidade de banners (máx 4).
 *
 * 1 → 1 coluna centralizada (pequena)
 * 2 → 2 colunas
 * 3 → 3 colunas
 * 4 → 4 colunas em linha no desktop, 2×2 no tablet
 */
const gridClass = computed(() => {
  const n = banners.value.length;
  if (n === 1) return 'grid-cols-1 place-items-center';
  if (n === 2) return 'grid-cols-1 sm:grid-cols-2 max-w-2xl mx-auto';
  if (n === 3) return 'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 max-w-5xl mx-auto';
  return 'grid-cols-2 lg:grid-cols-4 mx-auto'; // 4 → 1 linha no desktop
});

onMounted(fetchBanners);
</script>

<template>
  <section v-if="!loading && banners.length > 0"
    class="banners-section-bg bg-gray-100"
    aria-label="Publicações e portais oficiais"
  >
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-10">
      <!-- Cabeçalho -->
      <div class="section-header">
        <div class="header-line"></div>
        <h2 class="section-title">Acesse</h2>
        <div class="header-line"></div>
      </div>

      <!-- Skeleton de carregamento -->
      <template v-if="loading">
        <div :class="['grid gap-4 lg:gap-6', 'grid-cols-1 sm:grid-cols-2']">
          <div
            v-for="i in 2"
            :key="i"
            class="banner-card skeleton-loader"
            aria-hidden="true"
          />
        </div>
      </template>

      <!-- Sem banners cadastrados -->
      <template v-else-if="!loading && banners.length === 0">
        <!-- Seção omitida quando vazia -->
      </template>

      <!-- Grid de banners -->
      <template v-else>
        <div :class="['grid gap-4 lg:gap-6', gridClass]">
          <component
            :is="banner.link ? 'a' : 'div'"
            v-for="banner in banners"
            :key="banner.id"
            v-bind="banner.link
              ? {
                  href: banner.link,
                  target: banner.nova_aba ? '_blank' : '_self',
                  rel: banner.nova_aba ? 'noopener noreferrer' : undefined,
                  'aria-label': `Acessar ${banner.titulo}${banner.descricao ? ' - ' + banner.descricao : ''}`,
                }
              : { role: 'img', 'aria-label': banner.titulo }
            "
            class="banner-card group"
          >
            <!-- Skeleton individual enquanto imagem carrega -->
            <div v-if="!imageLoaded[banner.id]" class="skeleton-loader" />

            <!-- Imagem -->
            <img
              :src="banner.imagem"
              :alt="banner.titulo"
              :class="['banner-image', { loaded: imageLoaded[banner.id] }]"
              loading="lazy"
              @load="handleImageLoad(banner.id)"
            />

            <!-- Overlay no hover -->
            <div v-if="banner.link" class="banner-overlay" aria-hidden="true">
              <div class="overlay-content">
                <span class="overlay-action">
                  Acessar
                  <svg
                    class="w-4 h-4 ml-2"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="2"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                    />
                  </svg>
                </span>
              </div>
            </div>
          </component>
        </div>
      </template>
    </div>
  </section>
</template>

<style scoped>
/* Seção  */
.banners-section-bg {
  @apply py-6;
}

/* Impede interação com overlay */
.banners-section-bg * {
  pointer-events: auto;
}

/* Cabeçalho  */
.section-header {
  @apply flex items-center justify-center gap-4 mb-5;
}

.header-line {
  @apply flex-1 h-px bg-gray-300;
  max-width: 200px;
}

.section-title {
  @apply text-gray-800 font-semibold text-xl sm:text-2xl tracking-wide uppercase;
  @apply flex items-center whitespace-nowrap;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

/* Card do banner  */
.banner-card {
  @apply relative overflow-hidden rounded-xl shadow-md transition-all duration-300;
  @apply hover:shadow-2xl focus:outline-none focus:ring-2 focus:ring-blue-500;
  aspect-ratio: 1 / 1;
  max-height: 280px;
  display: block;
  will-change: box-shadow;
}

/* Skeleton loader */
.skeleton-loader {
  @apply absolute inset-0 bg-gradient-to-r from-gray-200 via-gray-300 to-gray-200;
  animation: shimmer 1.8s infinite linear;
  background-size: 1000px 100%;
  z-index: 1;
}

@keyframes shimmer {
  0%   { background-position: -1000px 0; }
  100% { background-position:  1000px 0; }
}

/* Imagem */
.banner-image {
  @apply w-full h-full object-cover opacity-0 transition-opacity duration-500;
  position: relative;
  z-index: 2;
}

.banner-image.loaded {
  @apply opacity-100;
}

/* Overlay */
.banner-overlay {
  @apply absolute inset-0 opacity-0 transition-all duration-300;
  background: linear-gradient(
    to bottom,
    rgba(0, 0, 0, 0.25) 0%,
    rgba(0, 0, 0, 0.65) 100%
  );
  z-index: 3;
}

.banner-card:hover  .banner-overlay,
.banner-card:focus  .banner-overlay {
  @apply opacity-100;
}

.overlay-content {
  @apply absolute inset-0 flex flex-col items-center justify-center p-6 text-center;
}

.overlay-title {
  @apply text-white text-2xl font-bold mb-3;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.overlay-action {
  @apply text-white text-base font-medium flex items-center;
  @apply bg-white/20 backdrop-blur-sm px-5 py-2.5 rounded-full;
  @apply transition-all duration-300;
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.banner-card:hover .overlay-action,
.banner-card:focus .overlay-action {
  @apply bg-white/30;
  transform: translateY(-2px);
}

/* Responsividade */
@media (max-width: 768px) {
  .banners-section-bg { @apply py-6; }

  .banner-card { margin: 0 auto; }

  .overlay-title  { @apply text-xl; }
  .overlay-action { @apply text-sm px-4 py-2; }

  .section-title {
    @apply text-xl;
    flex-direction: column;
    gap: 0.5rem;
  }

  .header-line { max-width: 100px; }
}

@media (max-width: 640px) {
  .overlay-title { @apply text-lg; }
}

@media (min-width: 1280px) {
  .banners-section-bg { @apply py-8; }
}

/* Acessibilidade */
@media (prefers-reduced-motion: reduce) {
  .banner-card,
  .banner-overlay,
  .overlay-action,
  .banner-image {
    @apply transition-none;
  }
  .skeleton-loader { animation: none; }
}
</style>