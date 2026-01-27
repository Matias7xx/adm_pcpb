<script setup>
import SideCard from './SideCard.vue';
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';

// Estado do componente
const noticias = ref([]);
const loading = ref(true);
const error = ref(null);
const retryCount = ref(0);
const mounted = ref(false);

// Configurações
const MAX_RETRIES = 1;
const RETRY_DELAY = 2000;
const MAX_PREVIEW_ITEMS = 5;

// Debounce controller para evitar múltiplas requisições
let abortController = null;

// Buscar notícias da API
const fetchNoticias = async (isRetry = false) => {
  try {
    if (abortController) {
      abortController.abort();
    }

    abortController = new AbortController();

    if (!isRetry) {
      loading.value = true;
    }
    error.value = null;

    const response = await fetch('/api/noticias-home', {
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
      },
      signal: abortController.signal,
      cache: 'no-cache',
    });

    if (!response.ok) {
      const errorMessage =
        response.status === 404
          ? 'Serviço de notícias indisponível'
          : `Erro ${response.status}: ${response.statusText || 'Não foi possível carregar as notícias'}`;
      throw new Error(errorMessage);
    }

    const data = await response.json();

    if (!Array.isArray(data)) {
      throw new Error('Formato de dados inválido recebido do servidor');
    }

    noticias.value = data
      .slice(0, MAX_PREVIEW_ITEMS)
      .map(noticia => ({
        ...noticia,
        id: noticia.id || Math.random().toString(36).substr(2, 9),
        titulo: noticia.titulo || 'Título não disponível',
        descricao_curta: noticia.descricao_curta || 'Descrição não disponível',
        data_publicacao: formatDate(noticia.data_publicacao),
        visualizacoes: parseInt(noticia.visualizacoes) || 0,
        destaque: Boolean(noticia.destaque),
      }))
      .filter(noticia => noticia.titulo !== 'Título não disponível');

    loading.value = false;
    retryCount.value = 0;
  } catch (err) {
    if (err.name === 'AbortError') {
      return;
    }

    console.error('Erro ao carregar notícias:', err);

    if (retryCount.value < MAX_RETRIES && mounted.value) {
      retryCount.value++;
      console.log(`Tentativa ${retryCount.value} de ${MAX_RETRIES}...`);
      setTimeout(() => {
        if (mounted.value) fetchNoticias(true);
      }, RETRY_DELAY);
    } else {
      error.value = err.message;
      loading.value = false;
    }
  }
};

const handleImageError = event => {
  event.target.style.display = 'none';
};

const formatDate = dateString => {
  if (!dateString) return 'Data não informada';

  try {
    let date;
    if (dateString.includes('/')) {
      const [day, month, year] = dateString.split('/');
      date = new Date(year, month - 1, day);
    } else {
      date = new Date(dateString);
    }

    if (isNaN(date.getTime())) {
      return dateString;
    }

    return date.toLocaleDateString('pt-BR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
    });
  } catch (e) {
    console.warn('Erro ao formatar data:', e);
    return dateString;
  }
};

const truncateText = (text, length = 80) => {
  if (!text || typeof text !== 'string') return '';
  if (text.length <= length) return text;

  const truncated = text.substring(0, length);
  const lastSpace = truncated.lastIndexOf(' ');

  if (lastSpace > length * 0.7) {
    return truncated.substring(0, lastSpace).trim() + '...';
  }

  return truncated.trim() + '...';
};

const hasDestaque = () => {
  return noticias.value.some(noticia => noticia.destaque);
};

const formatViews = views => {
  if (views >= 1000) {
    return (views / 1000).toFixed(1) + 'k';
  }
  return views.toString();
};

onMounted(() => {
  mounted.value = true;
  fetchNoticias();
});

onUnmounted(() => {
  mounted.value = false;
  if (abortController) {
    abortController.abort();
  }
});
</script>

<template>
  <section class="w-full" aria-labelledby="noticias-titulo">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Header da seção -->
      <div class="mb-6 md:mb-8 lg:mb-12">
        <div class="flex items-center gap-4">
          <hr class="flex-1 border-t border-gray-300" />
          <h2
            id="noticias-titulo"
            class="text-gray-700 font-semibold text-xl sm:text-2xl tracking-wide uppercase whitespace-nowrap"
          >
            Notícias
          </h2>
          <hr class="flex-1 border-t border-gray-300" />
        </div>
      </div>

      <!-- Conteúdo principal - CORRIGIDO: lg:flex-row -->
      <div class="flex flex-col lg:flex-row gap-6 md:gap-8 lg:gap-12">
        <!-- Coluna principal de notícias -->
        <main class="w-full">
          <!-- Estado de carregamento -->
          <div
            v-if="loading"
            class="flex flex-col justify-center items-center py-16 lg:py-24"
            aria-live="polite"
            aria-busy="true"
          >
            <div class="relative">
              <div
                class="animate-spin rounded-full h-16 w-16 border-4 border-gray-200 border-t-gray-400"
                role="status"
              ></div>
            </div>
            <p class="mt-4 text-gray-600 font-medium">Carregando notícias...</p>
            <span class="sr-only">Carregando notícias...</span>
          </div>

          <!-- Estado de erro -->
          <div
            v-else-if="error"
            class="bg-gradient-to-r from-red-50 to-red-100 border-l-4 border-red-400 rounded-lg p-6 mb-8 shadow-sm"
            aria-live="assertive"
          >
            <div class="flex items-start">
              <svg
                class="h-6 w-6 text-red-400 mt-1 mr-3 flex-shrink-0"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                aria-hidden="true"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                />
              </svg>
              <div class="flex-1">
                <h3 class="text-red-800 font-semibold text-lg">
                  Erro ao carregar notícias
                </h3>
                <p class="text-red-700 mt-2">{{ error }}</p>
                <div class="mt-4 flex flex-wrap gap-3">
                  <button
                    @click="fetchNoticias"
                    class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 focus:ring-4 focus:ring-red-200 focus:outline-none transition-all duration-200 shadow-sm"
                  >
                    <svg
                      class="w-4 h-4 mr-2"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                      />
                    </svg>
                    Tentar novamente
                  </button>
                  <Link
                    href="/noticias"
                    class="inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 focus:ring-4 focus:ring-gray-200 focus:outline-none transition-all duration-200"
                  >
                    Ver arquivo de notícias
                  </Link>
                </div>
              </div>
            </div>
          </div>

          <!-- Estado vazio -->
          <div
            v-if="!loading && !error && noticias.length === 0"
            class="flex flex-col items-center justify-center py-16 lg:py-24 text-center"
          >
            <div class="max-w-md">
              <svg
                class="w-16 h-16 mx-auto mb-4 text-gray-400"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                aria-hidden="true"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="1"
                  d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"
                />
              </svg>
              <h3 class="text-xl font-semibold text-gray-900 mb-2">
                Nenhuma notícia encontrada
              </h3>
              <p class="text-gray-500 mb-6">
                Não há notícias publicadas no momento. Volte em breve para
                conferir as novidades!
              </p>
              <Link
                href="/noticias"
                class="inline-flex items-center px-6 py-3 bg-[#bea55a] text-white font-medium rounded-lg hover:bg-yellow-600 focus:ring-4 focus:ring-yellow-200 focus:outline-none transition-all duration-200 shadow-md"
              >
                Explorar arquivo de notícias
              </Link>
            </div>
          </div>

          <!-- Grid de notícias -->
          <div v-else class="space-y-4">
            <article
              v-for="(noticia, index) in noticias"
              :key="noticia.id"
              class="group bg-gray-50 rounded-lg shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden border"
            >
              <div class="flex h-36 sm:h-44">
                <!-- Container da imagem -->
                <div
                  class="w-32 sm:w-40 md:w-44 relative overflow-hidden flex-shrink-0"
                  v-if="noticia.imagem"
                >
                  <img
                    :src="noticia.imagem"
                    :alt="`Imagem ilustrativa da notícia: ${noticia.titulo}`"
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                    loading="lazy"
                    @error="handleImageError"
                  />

                  <!-- Badge de destaque -->
                  <div
                    v-if="noticia.destaque"
                    class="absolute top-2 left-2 bg-gradient-to-r from-yellow-400 to-[#bea55a] text-white text-xs font-bold px-2 py-1 rounded-full shadow-sm"
                    aria-label="Notícia em destaque"
                  >
                    Destaque
                  </div>
                </div>

                <!-- Conteúdo -->
                <div
                  class="flex-1 p-3 sm:p-4 flex flex-col justify-between min-w-0"
                >
                  <div>
                    <!-- Metadados -->
                    <div class="flex items-center justify-between mb-1">
                      <div class="flex items-center text-xs text-gray-500">
                        <svg
                          class="h-3 w-3 mr-1 text-[#bea55a]"
                          fill="none"
                          viewBox="0 0 24 24"
                          stroke="currentColor"
                          aria-hidden="true"
                        >
                          <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                          />
                        </svg>
                        <time :datetime="noticia.data_publicacao">
                          {{ noticia.data_publicacao }}
                        </time>
                      </div>
                    </div>

                    <!-- Título -->
                    <Link :href="`/noticias/${noticia.id}`">
                      <h3
                        class="text-sm sm:text-base font-bold text-gray-900 mb-2 leading-tight line-clamp-2 group-hover:text-[#bea55a] transition-colors duration-300"
                      >
                        {{ noticia.titulo }}
                      </h3>
                    </Link>

                    <!-- Descrição -->
                    <p
                      class="text-xs sm:text-sm text-gray-600 leading-relaxed line-clamp-2"
                    >
                      {{ truncateText(noticia.descricao_curta, 300) }}
                    </p>
                  </div>

                  <!-- Ação -->
                  <div class="mt-2">
                    <Link
                      :href="`/noticias/${noticia.id}`"
                      class="inline-flex items-center text-[#bea55a] hover:text-yellow-600 font-medium text-xs sm:text-sm group-hover:gap-2 gap-1 transition-all duration-300"
                      :aria-label="`Leia a notícia completa: ${noticia.titulo}`"
                    >
                      Leia mais
                      <svg
                        class="h-3 w-3 transform group-hover:translate-x-0.5 transition-transform duration-300"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        aria-hidden="true"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M17 8l4 4m0 0l-4 4m4-4H3"
                        />
                      </svg>
                    </Link>
                  </div>
                </div>
              </div>
            </article>
          </div>

          <!-- Botão Mais Notícias -->
          <div class="text-center pt-6" v-if="noticias.length > 0">
            <Link
              href="/noticias"
              class="inline-flex w-40 h-14 items-center justify-center px-7 py-2.5 text-gray-800 font-bold rounded-full hover:from-yellow-600 hover:to-[#bea55a] border border-gray-800 focus:outline-none transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
            >
              Mais Notícias
            </Link>
          </div>
        </main>

        <!-- Sidebar (opcional - descomentar se tiver SideCard) -->
        <!-- <aside class="w-full lg:w-1/3">
          <div class="sticky top-8">
            <SideCard />
          </div>
        </aside> -->
      </div>
    </div>
  </section>
</template>

<style scoped>
/* Animações e transições suaves */
.transform {
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Animação de carregamento */
@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

@keyframes pulse {
  0%,
  100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.animate-spin {
  animation: spin 1s linear infinite;
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Placeholder de imagem */
.placeholder-image {
  opacity: 0.8;
  background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
  filter: grayscale(100%);
}

/* Efeitos de hover */
.group:hover .group-hover\:scale-105 {
  transform: scale(1.05);
}

.group:hover .group-hover\:translate-x-1 {
  transform: translateX(0.25rem);
}

/* Estados de foco */
a:focus-visible,
button:focus-visible {
  outline: 2px solid #bea55a;
  outline-offset: 2px;
}

/* Line clamp */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
