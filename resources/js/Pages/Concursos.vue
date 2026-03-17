<script setup>
import { Head } from '@inertiajs/vue3';
import Header from './Components/Header.vue';
import SiteNavbar from './Components/SiteNavbar.vue';
import Footer from './Components/Footer.vue';

const linksConcursos = [
  {
    id: 1,
    titulo: 'Concurso PC/PB - 2021',
    descricao: 'Informações completas sobre o edital e processo seletivo',
    url: 'https://www.cebraspe.org.br/concursos/pc_pb_21',
  },
  {
    id: 2,
    titulo: 'Concurso PC/PB - 2008',
    descricao: 'Informações completas sobre o edital e processo seletivo',
    url: 'http://www.cespe.unb.br/concursos/pcpb2008/',
  },
];
</script>

<template>
  <Head title="Concursos" />

  <div class="min-h-screen flex flex-col bg-gray-100">
    <Header />
    <SiteNavbar />

    <section class="w-full bg-gray-100 py-10">
      <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
        <!-- Título -->
        <div class="text-center">
          <h1 class="text-3xl sm:text-4xl font-bold text-gray-900">
            Concursos Anteriores
          </h1>
        </div>

        <!-- Cards -->
        <div class="grid sm:grid-cols-2 gap-6 max-w-3xl mx-auto">
          <a
            v-for="concurso in linksConcursos"
            :key="concurso.id"
            :href="concurso.url"
            target="_blank"
            rel="noopener noreferrer"
            class="group bg-white rounded-xl shadow-md hover:shadow-lg border-2 border-transparent hover:border-[#bea55a] transition-all duration-300 overflow-hidden"
          >
            <!-- Topo do card -->
            <div
              class="bg-gray-500 group-hover:bg-[#bea55a] transition-colors duration-300 p-5 text-white flex items-center justify-between"
            >
              <div class="flex items-center gap-3">
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-6 w-6 flex-shrink-0"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"
                  />
                </svg>
                <h3 class="text-lg font-semibold">{{ concurso.titulo }}</h3>
              </div>
              <svg
                xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5 opacity-75 flex-shrink-0"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"
                />
              </svg>
            </div>

            <!-- Corpo do card -->
            <div class="p-5">
              <p class="text-gray-600 leading-relaxed mb-4">
                {{ concurso.descricao }}
              </p>
              <span
                class="text-sm text-[#bea55a] font-medium group-hover:underline"
              >
                Clique para acessar →
              </span>
            </div>
          </a>
        </div>
      </div>
    </section>

    <Footer />
  </div>
</template>
