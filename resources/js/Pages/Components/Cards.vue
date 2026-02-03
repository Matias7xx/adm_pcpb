<template>
  <div class="cards-container">
    <!-- Card Mais Acessados -->
    <div>
      <!-- Título fora do card -->
      <div class="card-header">
        <div class="header-divider-container">
          <hr class="header-line" />
          <div class="header-content">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="header-icon"
              viewBox="0 0 20 20"
              fill="currentColor"
            >
              <path
                fill-rule="evenodd"
                d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z"
                clip-rule="evenodd"
              />
            </svg>
            <h2 class="header-title">MAIS ACESSADOS</h2>
          </div>
          <hr class="header-line" />
        </div>
      </div>

      <!-- Card -->
      <div class="card">
        <div class="card-divider">
          <div v-for="item in maisAcessados" :key="item.id" class="card-item">
            <a
              :href="item.link"
              target="_blank"
              rel="noopener noreferrer"
              class="card-link"
            >
              {{ item.titulo }}
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Card Outros Serviços -->
    <div>
      <!-- Título fora do card -->
      <div class="card-header">
        <div class="header-divider-container">
          <hr class="header-line" />
          <div class="header-content">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 20 20"
              fill="currentColor"
              class="header-icon"
            >
              <path
                fill-rule="evenodd"
                d="M12.5 2A1.5 1.5 0 0 0 11 3.5V5h3V3.5A1.5 1.5 0 0 0 12.5 2ZM5.5 3.5A1.5 1.5 0 0 1 7 2h6a1.5 1.5 0 0 1 1.5 1.5V5h1.75c.69 0 1.25.56 1.25 1.25v11.5A1.25 1.25 0 0 1 16.75 19H3.25A1.25 1.25 0 0 1 2 17.75V6.25c0-.69.56-1.25 1.25-1.25H5V3.5ZM6.5 10a.5.5 0 0 0 0 1h7a.5.5 0 0 0 0-1h-7ZM6.5 13a.5.5 0 0 0 0 1h7a.5.5 0 0 0 0-1h-7Z"
                clip-rule="evenodd"
              />
            </svg>
            <h2 class="header-title">OUTROS SERVIÇOS</h2>
          </div>
          <hr class="header-line" />
        </div>
      </div>

      <!-- Card -->
      <div class="card">
        <div class="card-divider">
          <div v-for="item in outros" :key="item.id" class="card-item">
            <a
              :href="item.link"
              :target="item.external ? '_blank' : '_self'"
              :rel="item.external ? 'noopener noreferrer' : ''"
              class="card-link"
            >
              {{ item.titulo }}
              <span v-if="item.novo" class="badge-novo"> Novo </span>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';

const maisAcessados = ref([
  {
    id: 1,
    titulo: 'BO - Boletim de Ocorrência Online',
    link: 'https://delegaciaonline.pc.pb.gov.br/',
  },
  {
    id: 2,
    titulo: '197 - Denúncia Anônima',
    link: 'https://197.pc.pb.gov.br/',
    requireAuth: true,
  },
  {
    id: 3,
    titulo: 'Carteira de Identidade Nacional (CIN) - 1ª Via',
    link: 'https://paraibadigital.pb.gov.br/14acb242-aee5-4abd-b57b-2f93d584c7dd',
  },
  {
    id: 4,
    titulo: 'Carteira de Identidade Nacional (CIN) - 2ª Via',
    link: 'https://paraibadigital.pb.gov.br/548f5b66-6e8b-42a5-b7c4-6c31623511c4',
  },
  {
    id: 5,
    titulo: 'Antecedentes Criminais',
    link: 'https://www.policiacivil.pb.gov.br/servicos/certidao-de-antecedentes-criminais',
  },
  {
    id: 6,
    titulo: 'Biblioteca de Normas',
    link: 'https://biblioteca.pc.pb.gov.br/',
  },
  {
    id: 7,
    titulo: 'Concurso Público',
    link: 'https://www.policiacivil.pb.gov.br/ingresso_e_promocao/concurso-publico',
  },
]);

const outros = ref([
  { id: 1, titulo: 'Acadepol', link: 'https://acadepol.pb.gov.br/', external: true },
  {
    id: 2,
    titulo: 'Portal do Servidor',
    link: 'https://portaldoservidor.pb.gov.br/',
    external: true,
  },
  {
    id: 3,
    titulo: 'Boletim Interno',
    link: 'https://policiacivil.pb.gov.br/boletim-interno-1',
    external: true,
  },
  { id: 4, titulo: 'Diário Oficial', link: 'https://acadepol.pb.gov.br/', external: true },
  {
    id: 5,
    titulo: 'Notícias',
    link: '/noticias',
    external: false,
  },
  {
    id: 6,
    titulo: 'PJe - Processo Judicial Eletrônico',
    link: 'https://www.policiacivil.pb.gov.br/links-uteis/pje-1',
    external: true,
  },
  {
    id: 7,
    titulo: 'Cadastro de Operações',
    link: '/operacoes',
    novo: false,
    external: false,
  },
]);
</script>

<style scoped>
/* Container principal - RESPONSIVO */
.cards-container {
  @apply grid grid-cols-1 md:grid-cols-2 gap-5 md:gap-6 lg:gap-8;
  @apply max-w-7xl mx-auto px-4 md:px-6 lg:px-8;
}

/* Header do card */
.card-header {
  @apply mb-3 md:mb-4;
}

.header-divider-container {
  @apply flex items-center gap-3 md:gap-4;
}

.header-line {
  @apply flex-1 border-t border-gray-300;
}

.header-content {
  @apply flex items-center gap-2 whitespace-nowrap;
}

.header-icon {
  @apply h-4 w-4 md:h-5 md:w-5 text-gray-700;
}

.header-title {
  @apply text-gray-700 font-semibold text-sm md:text-base tracking-wide uppercase;
}

/* Card */
.card {
  @apply bg-neutral-50 border-r-2 border-l-2 rounded-sm shadow-lg overflow-hidden;
}

.card-divider {
  @apply divide-y divide-gray-300;
}

.card-item {
  @apply hover:bg-gray-50 transition-colors duration-150;
}

/* Links - RESPONSIVO */
.card-link {
  @apply flex items-center py-3 px-4 md:py-4 md:px-6;
  @apply text-gray-700 font-medium text-sm md:text-base;
  @apply hover:text-[#bea55a] transition-colors;
}

/* Badge Novo */
.badge-novo {
  @apply ml-2 inline-flex items-center px-2 py-0.5 rounded;
  @apply text-xs font-medium bg-[#bea55a] text-black;
}

/* Mobile extra pequeno (< 375px) */
@media (max-width: 374px) {
  .cards-container {
    @apply px-3 gap-4;
  }

  .card-link {
    @apply py-2.5 px-3 text-xs;
  }

  .header-title {
    @apply text-xs;
  }

  .header-icon {
    @apply h-3.5 w-3.5;
  }
}

/* Tablet pequeno (768-1023px) */
@media (min-width: 768px) and (max-width: 1023px) {
  .cards-container {
    @apply gap-5;
  }

  .card-link {
    @apply py-3.5 px-5;
  }
}

/* Desktop grande (> 1280px) */
@media (min-width: 1280px) {
  .cards-container {
    @apply gap-10;
  }

  .card-link {
    @apply py-4 px-7;
  }

  .header-title {
    @apply text-lg;
  }
}

/* Redução de movimento */
@media (prefers-reduced-motion: reduce) {
  .card-item,
  .card-link {
    @apply transition-none;
  }
}

/* Estados de foco para acessibilidade */
.card-link:focus {
  @apply outline-none;
}

@media (hover: hover) {
  .card-item:hover {
    @apply bg-gray-100;
  }
}
</style>