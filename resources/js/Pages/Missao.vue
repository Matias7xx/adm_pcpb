<script setup>
import SiteNavbar from './Components/SiteNavbar.vue';
import Footer from './Components/Footer.vue';
import { Head } from '@inertiajs/vue3';
import Missao from '@/src/assets/BRASAOG.png';
import Missao2 from '@/src/assets/CIDADE2.jpeg';
import Header from './Components/Header.vue';

const missao = {
  title: 'MISSÃO INSTITUCIONAL',
  description:
    'Exercer, com excelência, as atribuições de polícia investigativa e judiciária para garantir a proteção de direitos, bem como a manutenção da segurança pública e da paz social.',
  image: Missao,
};

const visao = {
  title: 'VISÃO ESTRATÉGICA',
  description:
    'Tornar-se referência nacional em investigação criminal e ser reconhecida pela sociedade como uma instituição imprescindível no enfrentamento à criminalidade e a promoção da justiça.',
  image: Missao2,
};

const valores = [
  { label: 'PROTEÇÃO', description: 'à sociedade.' },
  { label: 'COMPROMISSO', description: 'com a verdade.' },
  { label: 'PROBIDADE', description: 'nas atividades.' },
  { label: 'BRAVURA', description: 'no enfrentamento à criminalidade.' },
];

const slogan = 'Investigar e Proteger';
</script>

<template>
  <Head title="Missão" />
  <div class="min-h-screen flex flex-col bg-gray-100">
    <Header />
    <SiteNavbar />
    <section class="w-full bg-gray-100 py-10">
      <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
        <!-- Missão e Visão -->
        <div
          class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-12 items-center"
        >
          <!-- Imagem Missão -->
          <div class="relative w-full overflow-hidden rounded-lg shadow-lg">
            <img
              :src="missao.image"
              :alt="missao.title"
              class="w-full h-auto object-cover transform transition-all duration-300 hover:scale-105"
            />
          </div>

          <!-- Texto Missão -->
          <div>
            <h2 class="text-3xl font-bold text-[#bea55a] mb-4">
              {{ missao.title }}
            </h2>
            <p class="text-gray-700 leading-relaxed text-justify italic">
              "{{ missao.description }}"
            </p>
          </div>

          <!-- Texto Visão -->
          <div>
            <h2 class="text-3xl font-bold text-[#bea55a] mb-4">
              {{ visao.title }}
            </h2>
            <p class="text-gray-700 leading-relaxed text-justify italic">
              "{{ visao.description }}"
            </p>
          </div>

          <!-- Imagem Visão -->
          <div class="relative w-full overflow-hidden rounded-lg shadow-lg">
            <img
              :src="visao.image"
              :alt="visao.title"
              class="w-full h-auto object-cover transform transition-all duration-300 hover:scale-105"
            />
          </div>
        </div>
      </div>
    </section>
    <Footer />
  </div>
</template>
