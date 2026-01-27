<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import axios from 'axios';

// Estados reativos
const videos = ref([]);
const loading = ref(true);
const error = ref(null);
const currentIndex = ref(0);

// Computed para navegação do carrossel
const canGoPrev = computed(() => currentIndex.value > 0);
const canGoNext = computed(
  () =>
    currentIndex.value <
    Math.ceil(videos.value.length / videosPerView.value) - 1
);

// Quantidade de vídeos visíveis por vez
const videosPerView = ref(3);

// Vídeos visíveis na tela atual
const visibleVideos = computed(() => {
  if (!videos.value || videos.value.length === 0) {
    return [];
  }

  const safeIndex = Math.max(0, currentIndex.value);
  const start = safeIndex * videosPerView.value;
  const end = start + videosPerView.value;

  return videos.value.slice(start, end);
});

// Total de páginas do carrossel
const totalPages = computed(() =>
  Math.ceil(videos.value.length / videosPerView.value)
);

// Layout especial para poucos vídeos
const layoutClass = computed(() => {
  const totalVideos = visibleVideos.value.length;
  if (totalVideos === 1) return 'justify-center';
  if (totalVideos === 2) return 'justify-center';
  return '';
});

const videoCardClass = computed(() => {
  const totalVideos = visibleVideos.value.length;
  if (totalVideos === 1) return 'max-w-2xl';
  if (totalVideos === 2) return 'max-w-md';
  return '';
});

// Buscar vídeos da API
const fetchVideos = async () => {
  try {
    loading.value = true;
    error.value = null;

    const response = await axios.get('/api/videos');
    videos.value = response.data;
  } catch (err) {
    console.error('Erro ao buscar vídeos:', err);
    error.value = 'Erro ao carregar vídeos. Tente novamente mais tarde.';
  } finally {
    loading.value = false;
  }
};

// Navegação
const goToPrev = () => {
  if (canGoPrev.value) {
    currentIndex.value--;
  }
};

const goToNext = () => {
  if (canGoNext.value) {
    currentIndex.value++;
  }
};

const goToPage = pageIndex => {
  if (pageIndex >= 0 && pageIndex < totalPages.value) {
    currentIndex.value = pageIndex;
  }
};

// Ajustar responsividade
const updateVideosPerView = () => {
  if (window.innerWidth < 768) {
    videosPerView.value = 1; // Mobile
  } else if (window.innerWidth < 1024) {
    videosPerView.value = 2; // Tablet
  } else {
    videosPerView.value = 3; // Desktop
  }

  const maxIndex = Math.ceil(videos.value.length / videosPerView.value) - 1;

  if (maxIndex < 0) {
    currentIndex.value = 0;
  } else if (currentIndex.value > maxIndex) {
    currentIndex.value = maxIndex;
  }
};

// Lifecycle hooks
onMounted(() => {
  fetchVideos();
  updateVideosPerView();
  window.addEventListener('resize', updateVideosPerView);
});

onBeforeUnmount(() => {
  window.removeEventListener('resize', updateVideosPerView);
});
</script>

<template>
  <section class="w-full video-section-bg bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Cabeçalho -->
      <div class="mb-8 lg:mb-12">
        <div class="flex items-center gap-4">
          <hr class="flex-1 border-t border-gray-300" />
          <h2
            class="text-gray-700 font-semibold text-xl sm:text-2xl tracking-wide uppercase whitespace-nowrap"
          >
            Vídeos
          </h2>
          <hr class="flex-1 border-t border-gray-300" />
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading-container">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="i in 3" :key="i" class="video-card-skeleton">
            <div class="skeleton-thumbnail"></div>
            <div class="skeleton-content">
              <div class="skeleton-line skeleton-title"></div>
              <div class="skeleton-line skeleton-description"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="error-container">
        <div class="error-content">
          <svg
            class="error-icon"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
          <p class="error-message">{{ error }}</p>
          <button @click="fetchVideos" class="retry-button">
            Tentar Novamente
          </button>
        </div>
      </div>

      <!-- Empty State -->
      <div
        v-if="!loading && !error && videos.length === 0"
        class="empty-container"
      >
        <div class="empty-content">
          <svg
            class="empty-icon"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"
            />
          </svg>
          <p class="empty-message">Nenhum vídeo disponível no momento.</p>
        </div>
      </div>

      <!-- Video Carousel -->
      <div
        v-if="!loading && !error && videos.length > 0"
        class="carousel-wrapper"
      >
        <div class="relative">
          <!-- Videos Grid -->
          <div class="videos-grid" :class="layoutClass">
            <div
              v-for="video in visibleVideos"
              :key="video.id"
              class="video-card"
              :class="videoCardClass"
            >
              <!-- YouTube Embed -->
              <div class="video-embed-container">
                <iframe
                  :src="video.embed_url"
                  :title="video.titulo"
                  frameborder="0"
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                  allowfullscreen
                  class="video-iframe"
                  loading="lazy"
                ></iframe>
              </div>
            </div>
          </div>

          <!-- Navigation Arrows -->
          <template v-if="totalPages > 1">
            <button
              @click="goToPrev"
              :disabled="!canGoPrev"
              class="nav-button nav-button-prev"
              :class="{ 'nav-button-disabled': !canGoPrev }"
              aria-label="Vídeos anteriores"
            >
              <svg
                class="w-6 h-6"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M15 19l-7-7 7-7"
                />
              </svg>
            </button>

            <button
              @click="goToNext"
              :disabled="!canGoNext"
              class="nav-button nav-button-next"
              :class="{ 'nav-button-disabled': !canGoNext }"
              aria-label="Próximos vídeos"
            >
              <svg
                class="w-6 h-6"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M9 5l7 7-7 7"
                />
              </svg>
            </button>
          </template>
        </div>

        <!-- Indicators -->
        <div v-if="totalPages > 1" class="indicators-container">
          <button
            v-for="(_, index) in totalPages"
            :key="index"
            @click="goToPage(index)"
            class="indicator"
            :class="{ 'indicator-active': index === currentIndex }"
            :aria-label="`Página ${index + 1}`"
          ></button>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.video-section-bg {
  @apply py-8 lg:py-12; /* ADICIONADO */
  /* background: linear-gradient(180deg, #f9fafb 0%, #f3f4f6 50%, #f9fafb 100%); */
  position: relative;
}

.icon-wrapper {
  @apply w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center;
}

.section-title {
  @apply text-2xl md:text-3xl font-bold text-gray-900;
}

.section-subtitle {
  @apply text-sm text-gray-600 mt-1;
}

/* Loading State */
.loading-container {
  @apply py-8;
}

.video-card-skeleton {
  @apply bg-gray-100 rounded-lg overflow-hidden animate-pulse;
}

.skeleton-thumbnail {
  @apply w-full aspect-video bg-gray-200;
}

.skeleton-content {
  @apply p-4 space-y-3;
}

.skeleton-line {
  @apply bg-gray-300 rounded;
}

.skeleton-title {
  @apply h-5 w-3/4;
}

.skeleton-description {
  @apply h-4 w-full;
}

/* Error State */
.error-container,
.empty-container {
  @apply py-16 text-center;
}

.error-content,
.empty-content {
  @apply max-w-md mx-auto;
}

.error-icon,
.empty-icon {
  @apply w-16 h-16 mx-auto mb-4 text-gray-400;
}

.error-message,
.empty-message {
  @apply text-gray-600 mb-4;
}

.retry-button {
  @apply px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2;
}

/* Carousel */
.carousel-wrapper {
  @apply relative;
}

.videos-grid {
  @apply grid grid-cols-1 gap-6 mb-6;
}

.videos-grid.justify-center {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
}

@media (min-width: 768px) {
  .videos-grid:not(.justify-center) {
    @apply grid-cols-2;
  }
}

@media (min-width: 1024px) {
  .videos-grid:not(.justify-center) {
    @apply grid-cols-3;
  }
}

/* Video Card */
.video-card {
  @apply bg-white rounded-lg overflow-hidden shadow-lg hover:shadow-xl transition-shadow duration-300;
}

/* Cards especiais para 1-2 vídeos */
.video-card.max-w-2xl {
  width: 100%;
}

@media (min-width: 640px) {
  .video-card.max-w-2xl {
    max-width: 42rem;
  }
}

.video-card.max-w-md {
  width: 100%;
}

@media (min-width: 640px) {
  .video-card.max-w-md {
    max-width: 28rem;
  }
}

.video-embed-container {
  @apply relative w-full aspect-video bg-black;
}

.video-iframe {
  @apply absolute inset-0 w-full h-full;
}

.video-info {
  @apply p-4;
}

.video-title {
  @apply text-lg font-semibold text-gray-900 mb-2 line-clamp-2;
}

.video-description {
  @apply text-sm text-gray-600 line-clamp-2;
}

/* Navigation */
.nav-button {
  @apply absolute top-1/2 -translate-y-1/2 bg-neutral-600 text-white p-3 rounded-full shadow-lg hover:bg-neutral-700 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-neutral-500 focus:ring-offset-2 z-10;
}

.nav-button-prev {
  @apply -left-4 md:-left-6;
}

.nav-button-next {
  @apply -right-4 md:-right-6;
}

.nav-button-disabled {
  @apply opacity-50 cursor-not-allowed hover:bg-neutral-600;
}

/* Indicators */
.indicators-container {
  @apply flex justify-center items-center space-x-2 mt-6;
}

.indicator {
  @apply w-2 h-2 rounded-full bg-gray-300 hover:bg-gray-400 transition-colors focus:outline-none focus:ring-2 focus:ring-neutral-500 focus:ring-offset-2;
}

.indicator-active {
  @apply w-8 bg-neutral-600 hover:bg-neutral-600;
}

/* Responsive */
@media (max-width: 768px) {
  .nav-button {
    @apply p-2;
  }

  .nav-button svg {
    @apply w-5 h-5;
  }

  .section-title {
    @apply text-xl;
  }

  .video-title {
    @apply text-base;
  }
}

/* Line clamp utility */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
