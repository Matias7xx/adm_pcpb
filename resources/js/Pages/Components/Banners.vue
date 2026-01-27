<script setup>
import { ref } from 'vue';

const bannerItems = ref([
  {
    id: 1,
    titulo: 'Paraíba da Gente',
    imagem: '/images/banners/banner1.png',
    link: 'https://heyzine.com/flip-book/22d36b7a40.html#page/1',
    alt: 'Banner Paraíba da Gente - Dezembro 2024',
    descricao: 'Revista digital com notícias e realizações',
  },
  {
    id: 2,
    titulo: 'Jornal A União',
    imagem: '/images/banners/banner2.png',
    link: 'https://auniao.pb.gov.br/',
    alt: 'Banner Jornal A União - Acesse o site',
    descricao: 'Portal de notícias oficial',
  },
]);

const imageLoaded = ref({});

const handleImageLoad = id => {
  imageLoaded.value[id] = true;
};
</script>

<template>
  <section
    class="banners-section-bg bg-gray-100"
    aria-label="Publicações e portais oficiais"
  >
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-10">
      <div class="section-header">
        <div class="header-line"></div>
        <h2 class="section-title">Acesse</h2>
        <div class="header-line"></div>
      </div>

      <!-- Descrição -->
      <p class="section-description">
        Confira a revista digital Paraíba da Gente e o Jornal A União
      </p>

      <!-- Grid de banners -->
      <div class="banners-grid">
        <a
          v-for="banner in bannerItems"
          :key="banner.id"
          :href="banner.link"
          :aria-label="`Acessar ${banner.titulo} - ${banner.descricao}`"
          target="_blank"
          rel="noopener noreferrer"
          class="banner-card group"
        >
          <!-- Skeleton loader -->
          <div v-if="!imageLoaded[banner.id]" class="skeleton-loader" />

          <!-- Imagem -->
          <img
            :src="banner.imagem"
            :alt="banner.alt || banner.titulo"
            class="banner-image"
            :class="{ loaded: imageLoaded[banner.id] }"
            loading="lazy"
            @load="handleImageLoad(banner.id)"
          />

          <!-- Overlay -->
          <div class="banner-overlay">
            <div class="overlay-content">
              <span class="overlay-title">{{ banner.titulo }}</span>
              <span class="overlay-action">
                Clique para acessar
                <svg
                  class="w-5 h-5 inline-block ml-1.5 transition-transform group-hover:translate-x-1"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                  aria-hidden="true"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                  />
                </svg>
              </span>
            </div>
          </div>
        </a>
      </div>
    </div>
  </section>
</template>

<style scoped>
/* Seção com background em largura total */
.banners-section-bg {
  @apply py-8 lg:py-12;
  /* background: linear-gradient(180deg, #f9fafb 0%, #f3f4f6 50%, #f9fafb 100%); */
  position: relative;
}

/* Textura de pontos sutil */
.banners-section-bg::before {
  content: '';
  position: absolute;
  inset: 0;
  background-image: radial-gradient(
    circle at 1px 1px,
    rgba(0, 0, 0, 0.02) 1px,
    transparent 0
  );
  background-size: 20px 20px;
  pointer-events: none;
}

/* Header com linha decorativa */
.section-header {
  @apply flex items-center justify-center gap-4 mb-4;
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

.section-description {
  @apply text-gray-600 text-center text-base sm:text-lg mb-10 lg:mb-12;
  @apply max-w-2xl mx-auto;
}

/* Grid de banners */
.banners-grid {
  @apply grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-10;
  position: relative;
  z-index: 1;
}

/* Card do banner */
.banner-card {
  @apply relative overflow-hidden rounded-xl shadow-md transition-all duration-300;
  @apply hover:shadow-2xl focus:outline-none;
  aspect-ratio: 1 / 1;
  display: block;
  max-width: 500px;
  margin: 0 auto;
  will-change: box-shadow;
}

/* Skeleton loader */
.skeleton-loader {
  @apply absolute inset-0 bg-gradient-to-r from-gray-200 via-gray-300 to-gray-200;
  animation: shimmer 2s infinite;
  z-index: 1;
}

@keyframes shimmer {
  0% {
    background-position: -500px 0;
  }
  100% {
    background-position: 500px 0;
  }
}

/* Imagem do banner */
.banner-image {
  @apply w-full h-full object-cover opacity-0 transition-opacity duration-500;
  position: relative;
  z-index: 2;
}

.banner-image.loaded {
  @apply opacity-100;
}

/* Overlay no hover */
.banner-overlay {
  @apply absolute inset-0 opacity-0 transition-all duration-300;
  background: linear-gradient(
    to bottom,
    rgba(0, 0, 0, 0.3) 0%,
    rgba(0, 0, 0, 0.7) 100%
  );
  z-index: 3;
}

.banner-card:hover .banner-overlay,
.banner-card:focus .banner-overlay {
  @apply opacity-100;
}

/* Conteúdo do overlay */
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

/* Responsividade - Mobile */
@media (max-width: 768px) {
  .banners-section-bg {
    @apply py-6; /* ESPAÇAMENTO REDUZIDO NO MOBILE */
  }

  .banners-grid {
    @apply gap-6;
  }

  .banner-card {
    max-width: 100%;
  }

  .overlay-title {
    @apply text-xl;
  }

  .overlay-action {
    @apply text-sm px-4 py-2;
  }

  .section-title {
    @apply text-xl;
    flex-direction: column;
    gap: 0.5rem;
  }

  .header-line {
    max-width: 100px;
  }
}

@media (max-width: 640px) {
  .section-description {
    @apply text-sm;
  }

  .overlay-title {
    @apply text-lg;
  }
}

/* Telas grandes */
@media (min-width: 1280px) {
  .banners-grid {
    @apply gap-12;
  }

  .banners-section-bg {
    @apply py-12; /* ESPAÇAMENTO REDUZIDO EM TELAS GRANDES */
  }
}

/* Acessibilidade - redução de movimento */
@media (prefers-reduced-motion: reduce) {
  .banner-card,
  .banner-overlay,
  .overlay-action,
  .banner-image {
    @apply transition-none;
  }

  .skeleton-loader {
    animation: none;
  }
}
</style>
