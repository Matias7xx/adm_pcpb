<script setup>
import SiteNavbar from './Components/SiteNavbar.vue';
import Footer from './Components/Footer.vue';
import Header from './Components/Header.vue';
import { Head } from '@inertiajs/vue3';

const secoes = [
  {
    titulo: 'O que é?',
    texto:
      'A Certidão de Antecedentes Criminais é um documento que mostra se o solicitante possui ou não algum processo criminal em tramitação na justiça paraibana. Ela é emitida, de forma gratuita, pelo Instituto de Polícia Científica da Paraíba (IPC/PB), que faz parte da estrutura da Polícia Civil.',
  },
  {
    titulo: 'Emissão da Certidão',
    texto:
      'Se nenhuma pendência for encontrada em nome do solicitante, ele receberá um e-mail com a cópia da Certidão, com a declaração que "nada consta" em processos criminais contra aquela pessoa tramitando na Justiça. O prazo para resposta é de até 72 horas. Se houver algum registro de processo criminal em curso, o solicitante da certidão será comunicado da situação e poderá apresentar outros documentos que forem necessários para modificar a declaração presente na certidão.',
  },
];

const passos = [
  'Envie um e-mail para: geicc@ssp.pb.gov.br',
  'No assunto, escreva "ANTECEDENTES CRIMINAIS".',
  'Anexe fotos legíveis (frente e verso) da Carteira de Identidade.',
];
</script>

<template>
  <Head title="Certidão de Antecedentes Criminais" />
  <div class="min-h-screen flex flex-col bg-gray-100">
    <Header />
    <SiteNavbar />

    <section class="w-full bg-gray-100 py-10">
      <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 space-y-10">
        <!-- Título principal -->
        <div class="text-center">
          <h1 class="text-3xl sm:text-4xl font-bold text-gray-900">
            Certidão de Antecedentes Criminais
          </h1>
          <!-- <div class="mt-3 mx-auto w-20 h-1 bg-[#bea55a] rounded"></div> -->
        </div>

        <!-- Seções de texto -->
        <div
          v-for="(secao, index) in secoes"
          :key="index"
          class="bg-white rounded-lg shadow-md p-6 sm:p-8"
        >
          <h2 class="text-2xl font-bold text-[#bea55a] mb-4">
            {{ secao.titulo }}
          </h2>
          <p class="text-gray-700 leading-relaxed text-justify">
            {{ secao.texto }}
          </p>
        </div>

        <!-- Como solicitar -->
        <div class="bg-white rounded-lg shadow-md p-6 sm:p-8">
          <h2 class="text-2xl font-bold text-[#bea55a] mb-6">Como solicitar</h2>
          <ol class="space-y-4">
            <li
              v-for="(passo, index) in passos"
              :key="index"
              class="flex items-start gap-4"
            >
              <span
                class="flex-shrink-0 w-8 h-8 rounded-full bg-[#bea55a] text-white flex items-center justify-center font-bold text-sm"
              >
                {{ index + 1 }}
              </span>
              <p class="text-gray-700 leading-relaxed pt-1">{{ passo }}</p>
            </li>
          </ol>
        </div>

        <!-- Contato -->
        <div class="bg-white rounded-lg shadow-md p-6 sm:p-8">
          <h2 class="text-2xl font-bold text-[#bea55a] mb-4">Contato</h2>
          <div class="flex items-center gap-3 text-gray-700">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="h-5 w-5 text-[#bea55a] flex-shrink-0"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 7V5z"
              />
            </svg>
            <span class="text-lg">(83) 3264-9158</span>
          </div>
        </div>
      </div>
    </section>

    <Footer />
  </div>
</template>
