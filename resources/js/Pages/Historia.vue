<script setup>
import SiteNavbar from './Components/SiteNavbar.vue';
import Footer from './Components/Footer.vue';
import { Head } from '@inertiajs/vue3';
import DG from '@/src/assets/DELEGACIA-GERAL.jpg';
import Central from '@/src/assets/CIDADE.jpeg';
import Draco from '@/src/assets/DRACO.jpeg';
import Header from './Components/Header.vue';

const historia = {
  title: 'Quando surgimos',
  description:
    'A polícia judiciária no Brasil remonta ao início do século XVII. Na Paraíba, a Polícia Civil segue o mesmo processo nacional, porém, apenas em 21 de agosto de 1981, é criada com estrutura de carreira pela Lei 4.273. A Lei Complementar n.º 85, publicada em 12 de agosto de 2008, é a Lei Orgânica que dispõe sobre a organização da Polícia Civil, suas obrigações perante a sociedade, seus deveres e regime disciplinar. É através desse instrumento que estão estabelecidas as categorias da Polícia Civil, o Plano de Cargos e Carreiras, e regras para o provimento de cargos a partir da realização de concursos (investidura, nomeação, posse). Versa sobre aposentadorias, promoções e honrarias que podem ser concedidas a membros que comprovem atos de bravuras, também sobre a atenção à saúde física e mental dos policiais, em função da salubridade da atividade. Está ordenada na Lei a prestação de assistência médico-psicológica quando necessário, em casos de desgastes emocionais ou distúrbios mentais resultantes do exercício da função.',
  image: DG,
};

const estrutura = {
  title: 'Nossa função',
  description:
    'Praticar, com exclusividade, todos os atos necessários ao exercício das funções de polícia judiciária e investigativa de caráter criminalístico e criminológico. No sistema da Polícia Investigadora, adotado pelo Brasil, cabe às polícias judiciárias (POLÍCIAS CIVIL e FEDERAL) a direção da investigação criminal, sendo estas as titulares do procedimento investigativo criminal, observados os limites de suas competências. Portanto, a Polícia Civil, dirigida por delegados de polícia de carreira, atua, via de regra, depois que um crime ocorre e busca através da investigação criminal estabelecer a verdade real dos fatos.',
  image: Draco,
};

const evolucao = {
  title: 'Princípios institucionais',
  description:
    'I – respeito ao Estado Democrático de Direito; II – garantia e promoção dos direitos e da dignidade da pessoa humana; III – obediência à hierarquia e à disciplina; IV – unidade de doutrina e uniformidade de procedimentos técnico-científicos, aplicados à investigação policial; V – participação comunitária; VI – integração, com reciprocidade, com os demais órgãos e agentes públicos que compõem o sistema de segurança pública.',
  image: Central,
};
</script>

<template>
  <Head title="História" />
  <div class="min-h-screen flex flex-col bg-gray-100">
    <Header />
    <SiteNavbar />

    <section class="w-full bg-gray-100 py-10">
      <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
        <!-- Bloco 1 -->
        <div
          class="flex flex-col md:grid md:grid-cols-2 gap-8 md:gap-12 items-center"
        >
          <!-- Imagem 1 -->
          <div class="w-full flex justify-center md:justify-start">
            <img
              :src="historia.image"
              :alt="historia.title"
              class="w-full max-w-sm md:max-w-full h-auto object-cover rounded-lg shadow-lg transform transition-all duration-300 hover:scale-105"
            />
          </div>

          <!-- Texto 1 -->
          <div class="text-left md:text-center">
            <h2 class="text-2xl sm:text-3xl font-bold text-[#bea55a] mb-4">
              {{ historia.title }}
            </h2>
            <p class="text-gray-700 leading-relaxed">
              {{ historia.description }}
            </p>
          </div>
        </div>

        <!-- Bloco 2 (Alternado) -->
        <div
          class="flex flex-col md:grid md:grid-cols-2 gap-8 md:gap-12 items-center"
        >
          <!-- Texto 2 -->
          <div class="text-center">
            <h2 class="text-2xl sm:text-3xl font-bold text-[#bea55a] mb-4">
              {{ estrutura.title }}
            </h2>
            <p class="text-gray-700 leading-relaxed">
              {{ estrutura.description }}
            </p>
          </div>

          <!-- Imagem 2 -->
          <div class="w-full flex justify-center md:justify-end">
            <img
              :src="estrutura.image"
              :alt="historia.title"
              class="w-full max-w-sm md:max-w-full h-auto object-cover rounded-lg shadow-lg transform transition-all duration-300 hover:scale-105"
            />
          </div>
        </div>

        <!-- Bloco 3 -->
        <div
          class="flex flex-col md:grid md:grid-cols-2 gap-8 md:gap-12 items-center"
        >
          <!-- Imagem 3 -->
          <div class="w-full flex justify-center md:justify-start">
            <img
              :src="evolucao.image"
              :alt="evolucao.title"
              class="w-full max-w-sm md:max-w-full h-auto object-cover rounded-lg shadow-lg transform transition-all duration-300 hover:scale-105"
            />
          </div>

          <!-- Texto 3 -->
          <div class="text-center md:text-center">
            <h2 class="text-2xl sm:text-3xl font-bold text-[#bea55a] mb-4">
              {{ evolucao.title }}
            </h2>
            <p class="text-gray-700 leading-relaxed">
              {{ evolucao.description }}
            </p>
          </div>
        </div>
      </div>
    </section>

    <Footer />
  </div>
</template>