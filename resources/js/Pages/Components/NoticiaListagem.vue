<script setup>
import { ref, onMounted, watch, onUnmounted } from 'vue';
import { Link, Head } from '@inertiajs/vue3';
import Header from './Header.vue';
import SiteNavbar from './SiteNavbar.vue';
import Footer from './Footer.vue';

// Estado do componente
const noticias = ref([]);
const loading = ref(true);
const error = ref(null);
const searchQuery = ref('');
const currentPage = ref(1);
const totalPages = ref(1);
const totalItems = ref(0);
const itemsPerPage = ref(9);
const retryCount = ref(0);
const mounted = ref(false);

// Configurações
const MAX_RETRIES = 2;
const RETRY_DELAY = 2000;

// Debounce controller para evitar múltiplas requisições
let abortController = null;

// Buscar notícias da API com paginação e busca
const fetchNoticias = async (isRetry = false) => {
  try {
    // Cancelar requisição anterior se existir
    if (abortController) {
      abortController.abort();
    }

    abortController = new AbortController();

    if (!isRetry) {
      loading.value = true;
    }
    error.value = null;

    // Construir URL com parâmetros
    let url = `/api/noticias?page=${currentPage.value}`;

    if (searchQuery.value) {
      url += `&search=${encodeURIComponent(searchQuery.value)}`;
    }

    const response = await fetch(url, {
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

    // Validar estrutura dos dados
    if (!data || !Array.isArray(data.data)) {
      throw new Error('Formato de dados inválido recebido do servidor');
    }

    // Processar dados da API paginada
    noticias.value = data.data
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

    // Atualizar informações de paginação
    currentPage.value = data.current_page || 1;
    totalPages.value = data.last_page || 1;
    totalItems.value = data.total || 0;
    itemsPerPage.value = data.per_page || 6;

    loading.value = false;
    retryCount.value = 0;

    // Atualizar URL com os parâmetros de busca e paginação
    updateUrlParams();
  } catch (err) {
    // Ignorar erros de abort (cancelamento de requisição)
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

// Atualizar parâmetros da URL sem recarregar a página
const updateUrlParams = () => {
  if (typeof window === 'undefined') return;

  const params = new URLSearchParams(window.location.search);

  if (currentPage.value !== 1) {
    params.set('page', currentPage.value);
  } else {
    params.delete('page');
  }

  if (searchQuery.value.trim()) {
    params.set('search', searchQuery.value.trim());
  } else {
    params.delete('search');
  }

  const url =
    window.location.pathname +
    (params.toString() ? `?${params.toString()}` : '');
  window.history.pushState({}, '', url);
};

// Tratamento de erro de imagem com fallback
const handleImageError = event => {
  event.target.style.display = 'none';
};

// Função para formatar datas com fallback
const formatDate = dateString => {
  if (!dateString) return 'Data não informada';

  try {
    // Tentar diferentes formatos de data
    let date;
    if (dateString.includes('/')) {
      const [day, month, year] = dateString.split('/');
      date = new Date(year, month - 1, day);
    } else {
      date = new Date(dateString);
    }

    if (isNaN(date.getTime())) {
      return dateString; // Retorna o valor original se não conseguir converter
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

// Truncar texto
const truncateText = (text, length = 100) => {
  if (!text || typeof text !== 'string') return '';
  if (text.length <= length) return text;

  const truncated = text.substring(0, length);
  const lastSpace = truncated.lastIndexOf(' ');

  if (lastSpace > length * 0.7) {
    return truncated.substring(0, lastSpace).trim() + '...';
  }

  return truncated.trim() + '...';
};

// Formatar número de visualizações
const formatViews = views => {
  if (views >= 1000) {
    return (views / 1000).toFixed(1) + 'k';
  }
  return views.toString();
};

// Realizar busca
const handleSearch = () => {
  currentPage.value = 1; // Resetar para página 1 ao buscar
  fetchNoticias();
};

// Limpar busca
const clearSearch = () => {
  searchQuery.value = '';
  handleSearch();
};

// Mudar página
const changePage = page => {
  if (page >= 1 && page <= totalPages.value && page !== currentPage.value) {
    currentPage.value = page;
    fetchNoticias();
    // Scroll suave para o topo da lista
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
};

// Inicializar a partir dos parâmetros da URL
const initFromUrl = () => {
  if (typeof window === 'undefined') return;

  const params = new URLSearchParams(window.location.search);

  // Pegar página da URL
  const pageParam = params.get('page');
  if (pageParam && !isNaN(parseInt(pageParam))) {
    currentPage.value = Math.max(1, parseInt(pageParam));
  }

  // Pegar termo de busca da URL
  const searchParam = params.get('search');
  if (searchParam) {
    searchQuery.value = searchParam.trim();
  }

  // Buscar notícias com esses parâmetros
  fetchNoticias();
};

// Configurar debounce para busca em tempo real
const debounce = (fn, delay) => {
  let timeoutId;
  return function (...args) {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => fn.apply(this, args), delay);
  };
};

const debouncedSearch = debounce(() => {
  if (mounted.value) {
    handleSearch();
  }
}, 500);

// Observar mudanças na query de busca
watch(searchQuery, (newValue, oldValue) => {
  // Só executar se o componente estiver montado e o valor realmente mudou
  if (mounted.value && newValue !== oldValue) {
    debouncedSearch();
  }
});

// Gerar array de páginas para paginação
const getPaginationPages = () => {
  const pages = [];
  const current = currentPage.value;
  const total = totalPages.value;

  if (total <= 7) {
    // Se temos 7 ou menos páginas, mostrar todas
    for (let i = 1; i <= total; i++) {
      pages.push(i);
    }
  } else {
    // Sempre mostrar primeira página
    pages.push(1);

    if (current > 3) {
      pages.push('...');
    }

    // Mostrar páginas ao redor da atual
    const start = Math.max(2, current - 1);
    const end = Math.min(total - 1, current + 1);

    for (let i = start; i <= end; i++) {
      pages.push(i);
    }

    if (current < total - 2) {
      pages.push('...');
    }

    // Sempre mostrar última página
    if (total > 1) {
      pages.push(total);
    }
  }

  return pages;
};

onMounted(() => {
  mounted.value = true;
  initFromUrl();
});

onUnmounted(() => {
  mounted.value = false;
  if (abortController) {
    abortController.abort();
  }
});
</script>

<template>
  <Head title="Notícias" />
  <Header />
  <SiteNavbar />

  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <Link
        href="/"
        class="inline-flex items-center text-sm text-gray-600 hover:text-[#bea55a] mb-6 transition-colors focus:outline-none"
      >
        <svg
          class="h-4 w-4 mr-2"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M10 19l-7-7m0 0l7-7m-7 7h18"
          />
        </svg>
        Voltar para Home
      </Link>

      <div
        class="mb-8 bg-white rounded-xl shadow-sm border border-gray-200 p-6 lg:p-8"
      >
        <div class="lg:flex lg:items-center lg:justify-between">
          <div class="flex-1">
            <p class="text-gray-600 text-md">
              Explore nosso arquivo completo e fique sempre atualizado sobre as
              atividades da Polícia Civil da Paraíba.
            </p>
          </div>

          <div class="mt-6 lg:mt-0 lg:ml-8 w-full lg:w-80">
            <div class="relative">
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Buscar notícias..."
                class="block w-full pl-10 pr-12 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#bea55a] focus:border-[#bea55a] transition-colors"
              />
              <div
                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
              >
                <svg
                  class="h-5 w-5 text-gray-400"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                  />
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div
        v-if="loading"
        class="flex flex-col justify-center items-center py-20 lg:py-32"
      >
        <div
          class="animate-spin rounded-full h-16 w-16 border-4 border-gray-200 border-t-[#bea55a]"
        ></div>
        <p class="mt-4 text-gray-600 font-medium">Carregando notícias...</p>
      </div>

      <template v-else>
        <div
          v-if="noticias.length > 0"
          class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8 lg:gap-10"
        >
          <article
            v-for="noticia in noticias"
            :key="noticia.id"
            class="group bg-white rounded-lg shadow-sm hover:shadow-md transition-all duration-300 border border-gray-100 flex flex-col h-full"
          >
            <!-- <div
              class="aspect-video relative overflow-hidden flex-shrink-0 bg-gray-50"
            >
              <img
                v-if="noticia.imagem"
                :src="noticia.imagem"
                :alt="noticia.titulo"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
              />
              <div
                v-else
                class="w-full h-full flex items-center justify-center text-gray-400 text-xs"
              >
                SEM IMAGEM
              </div>

              <div
                v-if="noticia.destaque"
                class="absolute top-3 left-3 bg-[#bea55a] text-white text-[10px] font-bold px-2 py-1 rounded shadow-sm z-10 uppercase"
              >
                Destaque
              </div>
            </div> -->

            <div class="p-5 flex flex-col flex-1">
              <div class="flex-1">
                <Link :href="`/noticias/${noticia.id}`">
                  <h3
                    class="text-lg font-bold text-[#bea55a] mb-4 leading-tight line-clamp-3 group-hover:text-[#a38d49] transition-colors duration-300"
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
                <div
                  class="text-[11px] text-gray-400 uppercase tracking-widest font-medium flex items-center"
                >
                  <svg
                    class="h-3 w-3 mr-1"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                    />
                  </svg>
                  {{ noticia.data_publicacao }}
                </div>
              </div>
            </div>
          </article>
        </div>

        <div
          v-else
          class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center"
        >
          <h3 class="text-xl font-semibold text-gray-900 mb-2">
            Nenhuma notícia encontrada
          </h3>
          <!-- <button
            @click="clearSearch"
            class="mt-4 px-6 py-2 bg-[#bea55a] text-white rounded-lg hover:bg-[#a38d49]"
          >
            Limpar busca
          </button> -->
        </div>

        <div
          v-if="totalPages > 1"
          class="flex flex-col sm:flex-row justify-between items-center mt-12 gap-4"
        >
          <div class="text-sm text-gray-600">
            Mostrando <b>{{ (currentPage - 1) * itemsPerPage + 1 }}</b> a
            <b>{{ Math.min(currentPage * itemsPerPage, totalItems) }}</b> de
            <b>{{ totalItems }}</b>
          </div>
          <nav class="inline-flex rounded-lg shadow-sm">
            <button
              @click="changePage(currentPage - 1)"
              :disabled="currentPage === 1"
              class="px-3 py-2 border border-gray-300 rounded-l-lg bg-white disabled:opacity-50"
            >
              «
            </button>
            <button
              v-for="page in getPaginationPages()"
              @click="typeof page === 'number' && changePage(page)"
              :class="
                page === currentPage
                  ? 'bg-[#bea55a] text-white border-[#bea55a]'
                  : 'bg-white text-gray-700 border-gray-300'
              "
              class="px-4 py-2 border transition-colors"
            >
              {{ page }}
            </button>
            <button
              @click="changePage(currentPage + 1)"
              :disabled="currentPage === totalPages"
              class="px-3 py-2 border border-gray-300 rounded-r-lg bg-white disabled:opacity-50"
            >
              »
            </button>
          </nav>
        </div>
      </template>
    </div>
  </div>
  <Footer />
</template>

<style scoped>
/* Animações e transições */
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

/* Estilos para placeholder de imagem */
.placeholder-image {
  opacity: 0.8;
  background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
  filter: grayscale(100%);
}

/* Limitar linhas de texto para layout compacto */
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  line-height: 1.4;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
  line-height: 1.4;
}

/* responsividade */
@media (max-width: 640px) {
  .aspect-video {
    aspect-ratio: 16 / 9;
  }
}

/* Efeitos de hover */
.group:hover .group-hover\:scale-105 {
  transform: scale(1.05);
}

.group:hover .group-hover\:gap-2 {
  gap: 0.5rem;
}

/* Estados de foco */
a:focus-visible,
button:focus-visible {
  outline: 2px solid #bea55a;
  outline-offset: 2px;
}

/* Gradientes */
.bg-gradient-to-br {
  background-image: linear-gradient(to bottom right, var(--tw-gradient-stops));
}

.bg-gradient-to-r {
  background-image: linear-gradient(to right, var(--tw-gradient-stops));
}

/* Transições para inputs */
input[type='text'] {
  transition:
    border-color 0.15s ease-in-out,
    box-shadow 0.15s ease-in-out;
}

input[type='text']:focus {
  border-color: #bea55a;
  box-shadow: 0 0 0 3px rgba(190, 165, 90, 0.1);
}

/* Estados de loading para botões */
button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Melhorar scrollbar em navegadores WebKit */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb {
  background: #bea55a;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: #a69247;
}
</style>
