<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
  autoPlayInterval: {
    type: Number,
    default: 5000,
  },
  height: {
    type: String,
    default: '200px',
  },
});

const banners = ref([]);
const loading = ref(true);
const currentIndex = ref(0);
const isPlaying = ref(true);
const autoPlayTimer = ref(null);
const isDragOver = ref(false);

const hasMultipleBanners = computed(() => banners.value.length > 1);

// BUSCAR BANNERS DA API
const fetchBanners = async () => {
  try {
    loading.value = true;
    const response = await axios.get('/banners');
    banners.value = response.data;
  } catch (error) {
    console.error('Erro ao carregar banners:', error);
    banners.value = [];
  } finally {
    loading.value = false;
  }
};

const nextSlide = () => {
  if (hasMultipleBanners.value) {
    currentIndex.value = (currentIndex.value + 1) % banners.value.length;
  }
};

const prevSlide = () => {
  if (hasMultipleBanners.value) {
    currentIndex.value =
      currentIndex.value === 0
        ? banners.value.length - 1
        : currentIndex.value - 1;
  }
};

const goToSlide = index => {
  currentIndex.value = index;
};

const startAutoPlay = () => {
  if (isPlaying.value && hasMultipleBanners.value) {
    autoPlayTimer.value = setInterval(() => {
      nextSlide();
    }, props.autoPlayInterval);
  }
};

const stopAutoPlay = () => {
  if (autoPlayTimer.value) {
    clearInterval(autoPlayTimer.value);
    autoPlayTimer.value = null;
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

// Touch handlers
let touchStartX = 0;
let touchEndX = 0;

const handleTouchStart = e => {
  touchStartX = e.changedTouches[0].screenX;
};

const handleTouchEnd = e => {
  touchEndX = e.changedTouches[0].screenX;
  handleSwipe();
};

const handleSwipe = () => {
  const swipeThreshold = 50;
  const diff = touchStartX - touchEndX;

  if (Math.abs(diff) > swipeThreshold) {
    if (diff > 0) {
      nextSlide();
    } else {
      prevSlide();
    }
  }
};

const handleBannerClick = banner => {
  if (banner.link) {
    if (banner.nova_aba) {
      window.open(banner.link, '_blank', 'noopener,noreferrer');
    } else {
      window.location.href = banner.link;
    }
  }
};

onMounted(() => {
  fetchBanners();
  startAutoPlay();
});

onUnmounted(() => {
  stopAutoPlay();
});
</script>

<template>
  <div v-if="!loading && banners.length > 0" class="banner-carousel-wrapper">
    <div
      class="banner-carousel-container"
      :style="{ height: height }"
      @mouseenter="stopAutoPlay"
      @mouseleave="isPlaying && startAutoPlay()"
      @touchstart="handleTouchStart"
      @touchend="handleTouchEnd"
      role="region"
      aria-label="Carrossel de banners"
    >
      <!-- Slides -->
      <div
        class="slides-container"
        :style="{ transform: `translateX(-${currentIndex * 100}%)` }"
      >
        <div
          v-for="(banner, index) in banners"
          :key="banner.id"
          class="slide-item"
          :class="{ 'slide-active': index === currentIndex }"
          @click="handleBannerClick(banner)"
          :style="{ cursor: banner.link ? 'pointer' : 'default' }"
        >
          <img
            :src="banner.imagem"
            :alt="banner.titulo"
            class="banner-image"
            :loading="index === currentIndex ? 'eager' : 'lazy'"
          />

          <!-- Overlay com título e descrição (opcional) -->
          <div
            v-if="banner.titulo || banner.descricao"
            class="banner-overlay"
          >
            <div class="banner-content">
              <!-- <h2 v-if="banner.titulo" class="banner-title">
                {{ banner.titulo }}
              </h2> -->
              <p v-if="banner.descricao" class="banner-description">
                {{ banner.descricao }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Controles de navegação -->
      <template v-if="hasMultipleBanners">
        <button
          @click="prevSlide"
          class="nav-button nav-button-prev"
          aria-label="Banner anterior"
        >
          <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2.5"
              d="M15 19l-7-7 7-7"
            />
          </svg>
        </button>

        <button
          @click="nextSlide"
          class="nav-button nav-button-next"
          aria-label="Próximo banner"
        >
          <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2.5"
              d="M9 5l7 7-7 7"
            />
          </svg>
        </button>
      </template>

      <!-- Indicadores -->
      <!-- <div v-if="hasMultipleBanners" class="indicators-container">
        <button
          v-for="(banner, index) in banners"
          :key="`indicator-${banner.id}`"
          @click="goToSlide(index)"
          class="indicator"
          :class="{ 'indicator-active': index === currentIndex }"
          :aria-label="`Ir para banner ${index + 1}`"
          :aria-current="index === currentIndex"
        >
          <div
            v-if="index === currentIndex && isPlaying"
            class="progress-fill"
            :style="{ animationDuration: `${autoPlayInterval}ms` }"
          ></div>
        </button>
      </div> -->

      <!-- Botão Play/Pause -->
      <!-- <button
        v-if="hasMultipleBanners"
        @click="toggleAutoPlay"
        class="play-pause-button"
        :aria-label="isPlaying ? 'Pausar carrossel' : 'Iniciar carrossel'"
      >
        <svg
          v-if="isPlaying"
          class="play-pause-icon"
          fill="currentColor"
          viewBox="0 0 24 24"
        >
          <path d="M6 4h4v16H6V4zm8 0h4v16h-4V4z" />
        </svg>
        <svg v-else class="play-pause-icon" fill="currentColor" viewBox="0 0 24 24">
          <path d="M8 5v14l11-7z" />
        </svg>
      </button> -->
    </div>
  </div>
</template>

<style scoped>
/* Container principal */
.banner-carousel-wrapper {
  width: 100%;
  margin: 0 auto;
}

.banner-carousel-container {
  position: relative;
  width: 100%;
  overflow: hidden;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
}

/* Slides */
.slides-container {
  display: flex;
  transition: transform 0.5s ease-in-out;
  height: 100%;
}

.slide-item {
  min-width: 100%;
  position: relative;
  height: 100%;
}

.banner-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

/* Overlay */
.banner-overlay {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  background: linear-gradient(
    to top,
    rgba(0, 0, 0, 0.8) 0%,
    rgba(0, 0, 0, 0.4) 50%,
    transparent 100%
  );
  padding: 2rem;
}

.banner-content {
  color: white;
  max-width: 1200px;
  margin: 0 auto;
}

.banner-title {
  font-size: 1.875rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
}

.banner-description {
  font-size: 1.125rem;
  opacity: 0.95;
  text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
}

/* Botões de navegação */
.nav-button {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background: transparent;
  color: white;
  padding: 0.5rem;
  border: none;
  cursor: pointer;
  z-index: 10;
  transition: all 0.3s ease;
  border-radius: 4px;
  filter: drop-shadow(0 0 2px rgba(0, 0, 0, 0.8));
}

.nav-button:hover {
  filter: drop-shadow(0 0 3px rgba(0, 0, 0, 1));
  transform: translateY(-50%) scale(1.1);
}

.nav-button:active {
  transform: translateY(-50%) scale(0.95);
}

.nav-button-prev {
  left: 1rem;
}

.nav-button-next {
  right: 1rem;
}

.nav-icon {
  width: 2rem;
  height: 2rem;
  stroke-width: 2.5;
  filter: drop-shadow(1px 1px 2px rgba(0, 0, 0, 0.8))
          drop-shadow(-1px -1px 2px rgba(0, 0, 0, 0.8));
}

/* Indicadores */
.indicators-container {
  position: absolute;
  bottom: 1rem;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  gap: 0.5rem;
  z-index: 10;
  padding: 0.5rem 1rem;
  border-radius: 2rem;
  background: rgba(0, 0, 0, 0.2);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
}

.indicator {
  width: 8px;
  height: 8px;
  background: rgba(255, 255, 255, 0.5);
  border: none;
  border-radius: 50%;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
  padding: 0;
}

.indicator:hover {
  background: rgba(255, 255, 255, 0.8);
  transform: scale(1.2);
}

.indicator-active {
  background: white;
  width: 24px;
  border-radius: 4px;
}

/* Barra de progresso */
.progress-fill {
  position: absolute;
  top: 0;
  left: 0;
  height: 100%;
  background: rgba(59, 130, 246, 0.8);
  animation: progress linear;
}

@keyframes progress {
  from {
    width: 0%;
  }
  to {
    width: 100%;
  }
}

/* Botão Play/Pause */
.play-pause-button {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: rgba(0, 0, 0, 0.2);
  backdrop-filter: blur(10px);
  -webkit-backdrop-filter: blur(10px);
  color: white;
  padding: 0.5rem;
  border-radius: 50%;
  border: 1px solid rgba(255, 255, 255, 0.2);
  cursor: pointer;
  transition: all 0.3s;
  z-index: 10;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.play-pause-button:hover {
  background: rgba(255, 255, 255, 0.95);
  color: #1f2937;
  transform: scale(1.1);
}

.play-pause-icon {
  width: 1rem;
  height: 1rem;
}

/* Responsivo */
@media (max-width: 768px) {
  .banner-title {
    font-size: 1.5rem;
  }

  .banner-description {
    font-size: 1rem;
  }

  .nav-button {
    padding: 0.375rem;
  }

  .nav-icon {
    width: 1.75rem;
    height: 1.75rem;
  }

  .banner-overlay {
    padding: 1rem;
  }

  .nav-button-prev {
    left: 0.5rem;
  }

  .nav-button-next {
    right: 0.5rem;
  }

  .indicators-container {
    bottom: 0.75rem;
    padding: 0.375rem 0.75rem;
  }

  .indicator {
    width: 6px;
    height: 6px;
  }

  .indicator-active {
    width: 18px;
  }
}

@media (max-width: 480px) {
  .nav-button {
    padding: 0.25rem;
  }

  .nav-icon {
    width: 1.5rem;
    height: 1.5rem;
  }

  .nav-button-prev {
    left: 0.25rem;
  }

  .nav-button-next {
    right: 0.25rem;
  }

  .indicator {
    width: 5px;
    height: 5px;
  }

  .indicator-active {
    width: 15px;
  }

  .play-pause-button {
    width: 32px;
    height: 32px;
  }

  .play-pause-icon {
    width: 0.875rem;
    height: 0.875rem;
  }
}

/* Modo de movimento reduzido */
@media (prefers-reduced-motion: reduce) {
  .slides-container,
  .nav-button,
  .indicator,
  .progress-fill {
    transition: none;
    animation: none !important;
  }
}
</style>
