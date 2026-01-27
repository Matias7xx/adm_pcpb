<script setup>
import {
  ref,
  onMounted,
  onBeforeUnmount,
  computed,
  watch,
  nextTick,
} from 'vue';

const props = defineProps({
  autoPlayDuration: {
    type: Number,
    default: 10000,
  },
  maxItems: {
    type: Number,
    default: 6,
  },
  lazy: {
    type: Boolean,
    default: true,
  },
});

const currentIndex = ref(0);
const autoPlayInterval = ref(null);
const newsItems = ref([]);
const loading = ref(true);
const error = ref(null);
const isPlaying = ref(true);
const touchStartX = ref(0);
const touchEndX = ref(0);
const retryCount = ref(0);
const maxRetries = 1;
const mounted = ref(false);
const isMobile = ref(false);

const imageStates = ref(new Map());
const preloadedImages = ref(new Set());

const PLACEHOLDER_LOCAL = '/images/placeholder-news2.png';
const PLACEHOLDER_CDN =
  'https://images.unsplash.com/photo-1586339949916-3e9457bef6d3?w=800&h=600&fit=crop&crop=center&auto=format&q=80&cs=tinysrgb';

const showSkeleton = computed(() => loading.value && mounted.value);
const showCarousel = computed(
  () => !loading.value && !error.value && newsItems.value.length > 0
);
const showEmpty = computed(
  () => !loading.value && !error.value && newsItems.value.length === 0
);

// Detectar mobile
const checkMobile = () => {
  isMobile.value = window.innerWidth < 768;
};

const itemsPerPage = computed(() => (isMobile.value ? 1 : 2));

const totalPages = computed(() =>
  Math.ceil(newsItems.value.length / itemsPerPage.value)
);

const hasMultiplePages = computed(() => totalPages.value > 1);
const showControls = computed(() => hasMultiplePages.value && !loading.value);

const currentPageNews = computed(() => {
  const start = currentIndex.value * itemsPerPage.value;
  const end = start + itemsPerPage.value;
  return newsItems.value.slice(start, end);
});

const isImageReady = (imageUrl, itemId) => {
  if (
    !imageUrl ||
    imageUrl === PLACEHOLDER_LOCAL ||
    imageUrl === PLACEHOLDER_CDN
  )
    return true;

  const cacheKey = `${itemId}-${imageUrl}`;
  return (
    preloadedImages.value.has(cacheKey) ||
    imageStates.value.get(cacheKey)?.loaded
  );
};

const preloadImage = (imageUrl, itemId) => {
  return new Promise((resolve, reject) => {
    if (
      !imageUrl ||
      imageUrl === PLACEHOLDER_LOCAL ||
      imageUrl === PLACEHOLDER_CDN
    ) {
      resolve();
      return;
    }

    const cacheKey = `${itemId}-${imageUrl}`;

    if (preloadedImages.value.has(cacheKey)) {
      resolve();
      return;
    }

    const img = new Image();

    img.onload = () => {
      preloadedImages.value.add(cacheKey);
      imageStates.value.set(cacheKey, { loaded: true, error: false });
      resolve();
    };

    img.onerror = () => {
      imageStates.value.set(cacheKey, { loaded: false, error: true });
      reject();
    };

    img.src = imageUrl;
  });
};

const fetchDestacadas = async (attempt = 1) => {
  try {
    if (attempt === 1 && !mounted.value) {
      loading.value = true;
    } else if (attempt > 1) {
      loading.value = true;
    }

    error.value = null;

    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 10000);

    const response = await fetch('/api/ultimas-noticias', {
      signal: controller.signal,
      headers: {
        Accept: 'application/json',
        'Cache-Control': 'no-cache',
      },
    });

    clearTimeout(timeoutId);

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }

    const data = await response.json();

    if (!Array.isArray(data)) {
      throw new Error('Formato de dados inválido recebido do servidor');
    }

    const destacadas = data.filter(item => item?.destaque && item?.id);
    const finalItems =
      destacadas.length > 0 ? destacadas : data.filter(item => item?.id);

    const formattedItems = finalItems
      .slice(0, props.maxItems)
      .map((noticia, index) => ({
        id: noticia.id || `item-${index}`,
        title: noticia.titulo || 'Título não disponível',
        excerpt: noticia.descricao_curta || '',
        image: noticia.imagem || PLACEHOLDER_LOCAL,
        originalImage: noticia.imagem,
        link: `/noticias/${noticia.id}`,
      }))
      .filter(item => item.title && item.id);

    newsItems.value = formattedItems;

    if (formattedItems.length > 0) {
      const firstTwo = formattedItems.slice(0, 2);

      for (const item of firstTwo) {
        if (item.originalImage && item.originalImage !== PLACEHOLDER_LOCAL) {
          try {
            await preloadImage(item.originalImage, item.id);
            item.image = item.originalImage;
          } catch (e) {
            console.warn('Falha ao carregar imagem, usando placeholder local');
            item.image = PLACEHOLDER_LOCAL;
          }
        }
      }

      formattedItems.slice(2).forEach(async item => {
        if (item.originalImage && item.originalImage !== PLACEHOLDER_LOCAL) {
          try {
            await preloadImage(item.originalImage, item.id);
            const itemIndex = newsItems.value.findIndex(
              newsItem => newsItem.id === item.id
            );
            if (itemIndex !== -1) {
              newsItems.value[itemIndex].image = item.originalImage;
            }
          } catch (e) {
            console.warn(
              `Falha ao carregar imagem do item ${item.id}, mantendo placeholder local`
            );
          }
        }
      });
    }

    if (currentIndex.value >= totalPages.value) {
      currentIndex.value = 0;
    }

    await nextTick();
    loading.value = false;
    retryCount.value = 0;
  } catch (err) {
    console.error(`Erro ao carregar notícias (tentativa ${attempt}):`, err);

    if (attempt < maxRetries && err.name !== 'AbortError') {
      retryCount.value = attempt;
      setTimeout(() => fetchDestacadas(attempt + 1), 2000 * attempt);
    } else {
      error.value =
        err.name === 'AbortError'
          ? 'Tempo limite excedido. Verifique sua conexão.'
          : `Erro ao carregar notícias: ${err.message}`;
      loading.value = false;
    }
  }
};

const nextSlide = () => {
  if (!hasMultiplePages.value) return;
  currentIndex.value = (currentIndex.value + 1) % totalPages.value;
};

const prevSlide = () => {
  if (!hasMultiplePages.value) return;
  currentIndex.value =
    (currentIndex.value - 1 + totalPages.value) % totalPages.value;
};

const goToSlide = index => {
  if (index >= 0 && index < totalPages.value) {
    currentIndex.value = index;
  }
};

const startAutoPlay = () => {
  if (!hasMultiplePages.value || !isPlaying.value) return;

  stopAutoPlay();
  autoPlayInterval.value = setInterval(() => {
    if (isPlaying.value && hasMultiplePages.value) {
      nextSlide();
    }
  }, props.autoPlayDuration);
};

const stopAutoPlay = () => {
  if (autoPlayInterval.value) {
    clearInterval(autoPlayInterval.value);
    autoPlayInterval.value = null;
  }
};

const toggleAutoPlay = () => {
  isPlaying.value = !isPlaying.value;
  if (isPlaying.value) {
    startAutoPlay();
  } else {
    stopAutoPlay();
  }
};

const handleTouchStart = e => {
  touchStartX.value = e.touches[0].clientX;
  stopAutoPlay();
};

const handleTouchEnd = e => {
  touchEndX.value = e.changedTouches[0].clientX;
  handleSwipe();
  if (isPlaying.value) {
    startAutoPlay();
  }
};

const handleSwipe = () => {
  const swipeThreshold = 50;
  const diff = touchStartX.value - touchEndX.value;

  if (Math.abs(diff) > swipeThreshold) {
    if (diff > 0) {
      nextSlide();
    } else {
      prevSlide();
    }
  }
};

const handleKeyDown = e => {
  if (!mounted.value) return;

  switch (e.key) {
    case 'ArrowLeft':
      e.preventDefault();
      prevSlide();
      break;
    case 'ArrowRight':
      e.preventDefault();
      nextSlide();
      break;
    case ' ':
      e.preventDefault();
      toggleAutoPlay();
      break;
    case 'Home':
      e.preventDefault();
      goToSlide(0);
      break;
    case 'End':
      e.preventDefault();
      goToSlide(totalPages.value - 1);
      break;
  }
};

const handleImageLoad = index => {
  const item = newsItems.value[index];
  if (item) {
    const cacheKey = `${item.id}-${item.image}`;
    imageStates.value.set(cacheKey, { loaded: true, error: false });
    preloadedImages.value.add(cacheKey);
  }
};

const handleImageError = (event, index) => {
  console.warn(`Erro ao carregar imagem do slide ${index}`);
  const item = newsItems.value[index];

  if (item) {
    const currentSrc = event.target.src;

    if (currentSrc !== PLACEHOLDER_LOCAL) {
      console.log('Tentando placeholder local');
      item.image = PLACEHOLDER_LOCAL;
    } else if (currentSrc === PLACEHOLDER_LOCAL) {
      console.log('Tentando placeholder CDN');
      item.image = PLACEHOLDER_CDN;
    }

    const cacheKey = `${item.id}-${item.image}`;
    imageStates.value.set(cacheKey, { loaded: false, error: true });
  }
};

const handleResize = () => {
  checkMobile();
  const maxIndex = totalPages.value - 1;
  if (currentIndex.value > maxIndex && maxIndex >= 0) {
    currentIndex.value = maxIndex;
  }
};

watch(
  () => newsItems.value.length,
  () => {
    const maxIndex = totalPages.value - 1;
    if (currentIndex.value > maxIndex && maxIndex >= 0) {
      currentIndex.value = maxIndex;
    }
  }
);

watch(isPlaying, value => {
  if (value && hasMultiplePages.value) {
    startAutoPlay();
  } else {
    stopAutoPlay();
  }
});

watch(hasMultiplePages, value => {
  if (value && isPlaying.value) {
    startAutoPlay();
  } else {
    stopAutoPlay();
  }
});

onMounted(async () => {
  mounted.value = true;
  checkMobile();
  await fetchDestacadas();

  window.addEventListener('resize', handleResize);
  document.addEventListener('keydown', handleKeyDown);

  if (hasMultiplePages.value && isPlaying.value) {
    startAutoPlay();
  }
});

onBeforeUnmount(() => {
  mounted.value = false;
  stopAutoPlay();
  window.removeEventListener('resize', handleResize);
  document.removeEventListener('keydown', handleKeyDown);
});
</script>

<template>
  <div class="carousel-section w-full relative">
    <!-- Skeleton Loading -->
    <div v-if="showSkeleton" class="carousel-skeleton">
      <div class="carousel-inner-container">
        <div class="skeleton-grid" :class="{ 'single-item': isMobile }">
          <div
            v-for="i in itemsPerPage"
            :key="`skeleton-${i}`"
            class="skeleton-card-simple"
          >
            <div class="skeleton-shimmer"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Carousel Container -->
    <div
      v-else-if="showCarousel"
      class="carousel-container"
      @touchstart="handleTouchStart"
      @touchend="handleTouchEnd"
      role="region"
      aria-label="Carrossel de notícias em destaque"
      aria-live="polite"
      aria-atomic="true"
    >
      <div class="carousel-inner-container">
        <div
          class="news-grid"
          :class="{ 'single-item': currentPageNews.length === 1 }"
        >
          <a
            v-for="(item, index) in currentPageNews"
            :key="`${item.id}-${currentIndex}`"
            :href="item.link"
            class="news-card"
            :aria-label="`Notícia: ${item.title}`"
          >
            <div class="card-image-container">
              <img
                :src="item.image"
                :alt="item.title"
                class="card-image"
                :class="{ 'image-loading': !isImageReady(item.image, item.id) }"
                loading="lazy"
                @load="handleImageLoad(index)"
                @error="e => handleImageError(e, index)"
              />

              <div class="card-overlay"></div>

              <div class="card-content">
                <h3 class="card-title">{{ item.title }}</h3>
                <span class="card-link">
                  Leia mais
                  <svg
                    class="link-arrow"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M9 5l7 7-7 7"
                    />
                  </svg>
                </span>
              </div>
            </div>
          </a>
        </div>
      </div>

      <template v-if="showControls">
        <button
          @click="prevSlide"
          class="nav-button nav-button-prev"
          aria-label="Notícias anteriores"
        >
          <svg
            class="nav-icon"
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
          @click="nextSlide"
          class="nav-button nav-button-next"
          aria-label="Próximas notícias"
        >
          <svg
            class="nav-icon"
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

        <!-- <button
          @click="toggleAutoPlay"
          class="play-pause-button"
          :aria-label="isPlaying ? 'Pausar carrossel' : 'Reproduzir carrossel'"
        >
          <svg
            v-if="isPlaying"
            class="play-pause-icon"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
          <svg
            v-else
            class="play-pause-icon"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"
            />
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            />
          </svg>
        </button> -->

        <div v-if="isPlaying" class="progress-bar">
          <div
            class="progress-fill"
            :style="{ animationDuration: `${props.autoPlayDuration}ms` }"
          ></div>
        </div>
      </template>

      <div v-if="hasMultiplePages" class="indicators-container">
        <button
          v-for="(_, index) in totalPages"
          :key="index"
          @click="goToSlide(index)"
          class="indicator"
          :class="{ 'indicator-active': index === currentIndex }"
          :aria-label="`Página ${index + 1} de ${totalPages}`"
        ></button>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="carousel-error">
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
        <p class="error-title">Erro ao carregar notícias</p>
        <p class="error-message">{{ error }}</p>
        <button @click="fetchDestacadas(1)" class="error-retry-btn">
          Tentar Novamente
        </button>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="showEmpty" class="carousel-empty">
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
            d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"
          />
        </svg>
        <p class="empty-title">Nenhuma notícia disponível</p>
        <p class="empty-message">Não há notícias em destaque no momento.</p>
      </div>
    </div>
  </div>
</template>

<style scoped>
.carousel-section {
  @apply max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative mb-12;
}

.carousel-container {
  @apply relative w-full overflow-visible;
}

.carousel-inner-container {
  @apply max-w-7xl mx-auto px-4 sm:px-6 lg:px-8;
  overflow: hidden;
}

.news-grid {
  @apply grid grid-cols-1 md:grid-cols-2 gap-4;
  margin: 0;
  padding: 0;
}

.news-grid.single-item {
  @apply md:grid-cols-1 md:max-w-xl md:mx-auto;
}

.news-card {
  @apply relative overflow-hidden rounded-lg shadow-lg;
  border: none;
  outline: none;
}

.card-image-container {
  @apply relative w-full;
  aspect-ratio: 3 / 4;
  max-height: 400px;
}

@media (max-width: 768px) {
  .card-image-container {
    max-height: 350px;
  }
}

.card-image {
  @apply w-full h-full object-cover transition-opacity duration-300;
  opacity: 1;
}

.image-loading {
  filter: brightness(0.98) contrast(1.02);
  transition: filter 0.5s ease-in-out;
}

.card-overlay {
  @apply absolute inset-0;
  background: linear-gradient(
    to top,
    rgba(0, 0, 0, 0.95) 0%,
    rgba(0, 0, 0, 0.75) 40%,
    rgba(0, 0, 0, 0.3) 70%,
    transparent 100%
  );
}

.card-content {
  @apply absolute bottom-0 left-0 right-0 p-3 sm:p-4 text-white z-10;
}

.card-title {
  @apply text-sm sm:text-base md:text-lg font-bold mb-2 line-clamp-2 leading-tight;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
}

.card-link {
  @apply inline-flex items-center text-white/90 hover:text-white transition-colors text-sm font-medium;
}

.link-arrow {
  @apply w-4 h-4 ml-1 transition-transform;
}

/* Skeleton Loading */
.carousel-skeleton {
  @apply relative overflow-hidden w-full rounded-lg;
}

.skeleton-grid {
  @apply grid grid-cols-1 md:grid-cols-2 gap-4;
}

.skeleton-grid.single-item {
  @apply md:grid-cols-1 md:max-w-xl md:mx-auto;
}

.skeleton-card-simple {
  @apply relative bg-gray-200 rounded-lg overflow-hidden;
  aspect-ratio: 3 / 4;
  max-height: 350px;
}

@media (max-width: 768px) {
  .skeleton-card-simple {
    max-height: 300px;
  }
}

.skeleton-shimmer {
  @apply absolute inset-0;
  background: linear-gradient(
    90deg,
    transparent 0%,
    rgba(255, 255, 255, 0.6) 50%,
    transparent 100%
  );
  animation: shimmer 2s infinite;
}

@keyframes shimmer {
  0% {
    transform: translateX(-100%);
  }
  100% {
    transform: translateX(100%);
  }
}

/* Estados de erro/vazio */
.carousel-error,
.carousel-empty {
  @apply relative shadow-xl overflow-hidden w-full h-64 sm:h-80 bg-gray-50 flex items-center justify-center rounded-lg border-2 border-dashed border-gray-200;
}

.error-content,
.empty-content {
  @apply text-center p-6 max-w-md;
}

.error-icon,
.empty-icon {
  @apply h-12 w-12 mx-auto mb-3 text-gray-400;
}

.error-title,
.empty-title {
  @apply text-lg font-semibold text-gray-700 mb-2;
}

.error-message,
.empty-message {
  @apply text-gray-500 mb-4;
}

.error-retry-btn {
  @apply inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2;
}

/* Controles de navegação */
.nav-button {
  @apply absolute top-1/2 -translate-y-1/2 bg-neutral-600 text-white p-3 rounded-full shadow-lg hover:bg-neutral-700 transition-all duration-300 focus:outline-none z-10;
}

.nav-button-prev {
  @apply left-0 md:left-0;
}

.nav-button-next {
  @apply right-0 md:right-0;
}

.nav-icon {
  @apply w-5 h-5 sm:w-6 sm:h-6;
}

.play-pause-button {
  @apply absolute top-2 right-0 md:right-0 bg-black/50 hover:bg-black/70 text-white p-2 rounded-full transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 z-20;
}

.play-pause-icon {
  @apply w-4 h-4;
}

.indicators-container {
  @apply absolute -bottom-8 left-1/2 -translate-x-1/2 flex space-x-2 z-20;
}

.indicator {
  @apply w-2 h-2 sm:w-3 sm:h-3 rounded-full transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2;
}

.indicator-active {
  @apply bg-[#bea55a] scale-110;
}

.indicator:not(.indicator-active) {
  @apply bg-black/75 hover:bg-black/70 hover:scale-105;
}

.progress-bar {
  @apply absolute bottom-0 left-0 right-0 h-1 bg-transparent z-10;
  overflow: hidden;
}

.progress-fill {
  @apply h-full bg-red-600 w-full transform origin-left scale-x-0;
  animation: progress linear forwards;
}

@keyframes progress {
  from {
    transform: scaleX(0);
  }
  to {
    transform: scaleX(1);
  }
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

@media (max-width: 768px) {
  .card-content {
    @apply p-2;
  }

  .card-title {
    @apply text-xs line-clamp-2;
  }

  .card-link {
    @apply text-xs;
  }

  .link-arrow {
    @apply w-3 h-3;
  }
}

@media (hover: hover) {
  .card-link:hover .link-arrow {
    @apply translate-x-1;
  }

  .nav-button:hover {
    @apply scale-110;
  }

  .indicator:hover {
    @apply scale-110;
  }

  .news-card:hover .card-image {
    @apply scale-105;
    transition: transform 0.6s ease-out;
  }
}

@media (prefers-reduced-motion: reduce) {
  .card-image,
  .nav-button,
  .indicator,
  .link-arrow,
  .carousel-container {
    @apply transition-none;
    animation: none !important;
  }

  .progress-fill,
  .skeleton-shimmer {
    animation: none;
  }
}

.card-image {
  will-change: opacity, transform;
  backface-visibility: hidden;
  transform: translateZ(0);
}

.sr-only {
  @apply absolute w-px h-px p-0 -m-px overflow-hidden whitespace-nowrap border-0;
  clip: rect(0, 0, 0, 0);
}
</style>
