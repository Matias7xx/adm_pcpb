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
const MAX_PREVIEW_ITEMS = 3;

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

      <main class="w-full">
        <div
          v-if="loading"
          class="flex flex-col justify-center items-center py-16 lg:py-24"
        >
          <div
            class="animate-spin rounded-full h-16 w-16 border-4 border-gray-200 border-t-[#bea55a]"
          ></div>
          <p class="mt-4 text-gray-600 font-medium">Carregando notícias...</p>
        </div>

        <div
          v-else-if="noticias.length > 0"
          class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8 lg:gap-10"
        >
          <article
            v-for="noticia in noticias"
            :key="noticia.id"
            class="group bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 border border-gray-100 flex flex-col h-full"
          >
            <div class="aspect-video relative overflow-hidden flex-shrink-0">
              <img
                v-if="noticia.imagem"
                :src="noticia.imagem"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                @error="handleImageError"
              />
              <div
                v-else
                class="w-full h-full bg-gray-100 flex items-center justify-center text-gray-400 text-sm"
              >
                Sem imagem
              </div>
            </div>

            <div class="p-5 flex flex-col flex-1">
              <div class="flex-1">
                <Link :href="`/noticias/${noticia.id}`">
                  <h3
                    class="text-lg font-bold text-[#bea55a] mb-4 leading-tight line-clamp-3 group-hover:text-[#a38d49] transition-colors"
                  >
                    {{ noticia.titulo }}
                  </h3>
                </Link>
              </div>

              <div
                class="mt-auto pt-4 border-t border-gray-100 flex flex-col gap-2"
              >
                <Link
                  :href="`/noticias/${noticia.id}`"
                  class="text-sm font-bold uppercase tracking-wider text-gray-800 hover:text-[#bea55a] flex items-center gap-1 transition-colors"
                >
                  SAIBA MAIS »
                </Link>
                <time
                  class="text-[11px] text-gray-400 uppercase tracking-widest font-medium"
                >
                  {{ noticia.data_publicacao }}
                </time>
              </div>
            </div>
          </article>
        </div>

        <div class="text-center pt-10" v-if="noticias.length > 0">
          <Link
            href="/noticias"
            class="inline-flex w-40 h-14 items-center justify-center px-7 py-2.5 text-gray-800 font-bold rounded-full border border-gray-800 focus:outline-none transition-all duration-300 shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
          >
            Mais Notícias
          </Link>
        </div>
      </main>
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
