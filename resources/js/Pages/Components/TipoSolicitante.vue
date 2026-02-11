<script setup>
import { Link, Head } from '@inertiajs/vue3';
import LayoutGuest from '@/Layouts/Admin/LayoutGuest.vue';
import SectionFullScreen from '@/Components/SectionFullScreen.vue';
import CardBox from '@/Components/CardBox.vue';
import BaseLevel from '@/Components/BaseLevel.vue';
import BaseDivider from '@/Components/BaseDivider.vue';
import Header from '../Components/Header.vue';
import SiteNavbar from '../Components/SiteNavbar.vue';
import Footer from '../Components/Footer.vue';
</script>

<template>
  <LayoutGuest>
    <Head title="Carteira de Identidade Nacional" />
    <SiteNavbar />
    <Header />
    <SectionFullScreen
      v-slot="{ cardClass }"
      bg="white"
      class="!min-h-0 py-8 md:py-36"
    >
      <CardBox :class="[cardClass, 'px-4 md:px-6 my-0']">
        <!-- Logo -->
        <!-- <div class="flex items-center mt-2 justify-center">
          <img
            src="/images/logoPC.png"
            class="w-32 md:w-36 lg:w-36 mx-auto"
            alt="PCPB"
          />
        </div> -->

        <!-- Título e Descrição -->
        <div
          class="w-full mt-6 px-1 py-1 overflow-hidden text-center flex flex-col items-center"
        >
          <h1 class="text-xl md:text-2xl font-bold text-gray-900 mb-2">
            Carteira de Identidade Nacional
          </h1>
          <p class="text-sm md:text-base text-gray-600 mb-4 md:mb-6">
            Escolha 1ª ou 2ª Via para continuar
          </p>
        </div>

        <!-- Opções de Solicitante -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
          <a
            href="https://paraibadigital.pb.gov.br/14acb242-aee5-4abd-b57b-2f93d584c7dd#error=login_required&state=c1a000de-4dbf-47bc-9d39-f5967253e911"
            target="_blank"
            class="group bg-gray-50 border-2 border-transparent hover:border-[#bea54a] rounded-xl p-6 transition-all duration-300 text-center shadow-sm hover:shadow-md"
          >
            <div class="text-[#bea54a] mb-3">
              <span class="text-3xl font-bold">1ª</span>
            </div>
            <h3 class="font-bold text-gray-800 uppercase tracking-wider">
              Primeira Via
            </h3>
            <p class="text-xs text-gray-500 mt-2">
              Solicitar documento pela primeira vez
            </p>
          </a>

          <a
            href="https://paraibadigital.pb.gov.br/548f5b66-6e8b-42a5-b7c4-6c31623511c4#error=login_required&state=d30826cc-c7d5-47b9-b13f-cf3b2a0637c9"
            target="_blank"
            class="group bg-gray-50 border-2 border-transparent hover:border-[#bea54a] rounded-xl p-6 transition-all duration-300 text-center shadow-sm hover:shadow-md"
          >
            <div class="text-[#bea54a] mb-3">
              <span class="text-3xl font-bold">2ª</span>
            </div>
            <h3 class="font-bold text-gray-800 uppercase tracking-wider">
              Segunda Via
            </h3>
            <p class="text-xs text-gray-500 mt-2">
              Perda, roubo ou atualização de dados
            </p>
          </a>
        </div>

        <BaseDivider />

        <!-- Botão Voltar -->
        <BaseLevel>
          <div></div>
          <Link :href="route('home')" class="hover:text-[#a38e5d] text-sm">
            ← Voltar à página inicial
          </Link>
        </BaseLevel>
      </CardBox>
    </SectionFullScreen>
  </LayoutGuest>
  <Footer />
</template>
