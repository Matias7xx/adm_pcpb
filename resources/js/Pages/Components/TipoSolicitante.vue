<script setup>
import { Link, Head } from '@inertiajs/vue3';
import Header from '../Components/Header.vue';
import SiteNavbar from '../Components/SiteNavbar.vue';
import Footer from '../Components/Footer.vue';

const opcoes = [
  {
    via: '1ª',
    titulo: 'Primeira Via',
    descricao: 'Solicitar documento pela primeira vez',
    href: 'https://paraibadigital.pb.gov.br/14acb242-aee5-4abd-b57b-2f93d584c7dd',
  },
  {
    via: '2ª',
    titulo: 'Segunda Via',
    descricao: 'Perda, roubo ou atualização de dados',
    href: 'https://paraibadigital.pb.gov.br/548f5b66-6e8b-42a5-b7c4-6c31623511c4',
  },
];
</script>

<template>
  <Head title="Carteira de Identidade Nacional" />
  <div class="min-h-screen flex flex-col bg-gray-100">
    <Header />
    <SiteNavbar />

    <section class="flex-1 flex items-center justify-center py-12 px-4">
      <div class="w-full max-w-lg">
        <!-- Cabeçalho -->
        <div class="text-center mb-8">
          <h1 class="text-3xl font-bold text-gray-900 mb-2">
            Carteira de Identidade Nacional
          </h1>
          <p class="text-gray-500">Escolha a opção desejada para continuar</p>
        </div>

        <!-- Cards de opção -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
          <a
            v-for="opcao in opcoes"
            :key="opcao.via"
            :href="opcao.href"
            target="_blank"
            class="group bg-white border-2 border-transparent hover:border-[#bea55a] rounded-xl p-8 transition-all duration-300 text-center shadow-sm hover:shadow-md"
          >
            <div class="text-[#bea55a] mb-3">
              <span class="text-4xl font-bold">{{ opcao.via }}</span>
            </div>
            <h3 class="font-bold text-gray-800 uppercase tracking-wider mb-2">
              {{ opcao.titulo }}
            </h3>
            <p class="text-sm text-gray-500">{{ opcao.descricao }}</p>
          </a>
        </div>

        <!-- Voltar -->
        <div class="text-center">
          <Link
            :href="route('home')"
            class="text-sm text-gray-500 hover:text-[#bea55a] transition-colors duration-200"
          >
            ← Voltar à página inicial
          </Link>
        </div>
      </div>
    </section>

    <Footer />
  </div>
</template>
