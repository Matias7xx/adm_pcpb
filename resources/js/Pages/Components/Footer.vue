<script setup>
import SocialMedia from './SocialMedia.vue';
import governoPb from '@/src/assets/governopb.png';
</script>

<template>
  <footer class="bg-black text-gray-300" role="contentinfo">
    <div class="max-w-7xl mx-auto px-4 md:px-6 lg:px-8 py-8 md:py-12">
      <!-- Grid Principal -->
      <div
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-12"
      >
        <!-- Coluna 1: Links Úteis -->
        <div class="space-y-4">
          <h2
            class="text-base font-semibold text-white tracking-wide uppercase"
          >
            Links Úteis
          </h2>
          <ul class="space-y-2.5">
            <li>
              <a
                href="/cursos"
                class="text-sm text-gray-400 hover:text-[#bea55a] transition-colors duration-200 inline-block"
              >
                Cursos
              </a>
            </li>
            <li>
              <a
                href="/fale-conosco"
                class="text-sm text-gray-400 hover:text-[#bea55a] transition-colors duration-200 inline-block"
              >
                Contato
              </a>
            </li>
            <li>
              <a
                href="/regimento-interno"
                class="text-sm text-gray-400 hover:text-[#bea55a] transition-colors duration-200 inline-block"
              >
                Regimento Interno
              </a>
            </li>
            <li>
              <a
                href="/organograma"
                class="text-sm text-gray-400 hover:text-[#bea55a] transition-colors duration-200 inline-block"
              >
                Organograma
              </a>
            </li>
            <li>
              <a
                href="/estrutura"
                class="text-sm text-gray-400 hover:text-[#bea55a] transition-colors duration-200 inline-block"
              >
                Estrutura
              </a>
            </li>
          </ul>
        </div>

        <!-- Coluna 2: Endereço -->
        <div class="space-y-4">
          <h2
            class="text-base font-semibold text-white tracking-wide uppercase"
          >
            Endereço
          </h2>
          <div class="text-sm text-gray-400 space-y-3">
            <address class="not-italic leading-relaxed">
              Delegacia Geral de Polícia Civil<br />
              Av. Gen. Edson Ramalho, nº 695<br />
              Manaíra - João Pessoa/PB<br />
              CEP: 58038-100
            </address>
            <div>
              <p class="font-medium text-gray-300 mb-1">Telefone</p>
              <a
                href="tel:+558336128600"
                class="text-gray-400 hover:text-[#bea55a] transition-colors duration-200"
              >
                (83) 3612-8600
              </a>
            </div>
          </div>
        </div>

        <!-- Coluna 3: Redes Sociais -->
        <div class="space-y-4">
          <h2
            class="text-base font-semibold text-white tracking-wide uppercase"
          >
            Redes Sociais
          </h2>
          <div class="flex justify-center md:justify-start">
            <SocialMedia
              class="!flex-nowrap"
              colorSetup="fill-gray-400 relative z-10 transition-all duration-300 group-hover:fill-white"
              xConfig="absolute top-full left-0 w-full h-full rounded-full bg-gray-700 z-0 transition-all duration-300 group-hover:top-0"
            />
          </div>
        </div>

        <!-- Coluna 4: Logo do Governo -->
        <div class="flex flex-col items-center md:items-start space-y-4">
          <!-- <h2 class="text-base font-semibold text-white tracking-wide uppercase">Governo</h2> -->
          <a
            href="https://paraiba.pb.gov.br/"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-block transform transition-all duration-200 hover:scale-105 hover:opacity-90"
          >
            <img
              class="h-14 w-auto"
              :src="governoPb"
              alt="Logo Governo da Paraíba"
            />
          </a>
        </div>
      </div>

      <!-- Divisor -->
      <div class="mt-10 mb-6 border-t border-gray-800"></div>

      <!-- Copyright e Créditos -->
      <div
        class="flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-gray-500"
      >
        <p class="text-center md:text-left">
          &copy; {{ new Date().getFullYear() }} Polícia Civil da Paraíba. Todos
          os direitos reservados.
        </p>
        <p class="text-center md:text-right">
          Desenvolvido por
          <span class="text-[#bea55a] font-medium"
            >Diretoria de Tecnologia da Informação</span
          >
        </p>
      </div>
    </div>
  </footer>
</template>

<style scoped>
/* Mobile: Centralizar conteúdo */
@media (max-width: 767px) {
  .space-y-4,
  .flex.flex-col.space-y-4 {
    text-align: center;
  }
}
</style>
