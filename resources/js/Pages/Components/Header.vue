<script setup>
import logoPC from '@/src/assets/logo-pcpb2.png';
import logoGOV from '@/src/assets/governopb.png';
import Search from './Search.vue';
import { Link } from '@inertiajs/vue3';
import SocialMedia from './SocialMedia.vue';
</script>

<template>
  <div class="bg-[black] py-5 md:py-7 lg:py-8 shadow-md">
    <div
      class="max-w-screen-xl mx-auto px-4 md:px-8 lg:px-14 flex items-center justify-between"
    >
      <!-- Logo PC - Esquerda -->
      <div class="flex items-center">
        <Link :href="route('home')" class="shrink-0">
          <img
            class="h-14 sm:h-16 md:h-24 w-auto"
            :src="logoPC"
            alt="Logo PC"
          />
        </Link>
        <!-- <div class="border-l border-white h-8 hidden sm:block"></div>
            <span class="text-white text-2xl font-bold pl-2 border-b-2 border-[#bea55a] pb-1 hidden sm:inline-block uppercase">Investigar e Proteger</span> -->
      </div>

      <!-- Logo GOV - Direita -->
      <div class="flex items-center">
        <Link :href="route('home')" class="shrink-0">
          <img
            class="h-12 sm:h-14 md:h-20 w-auto"
            :src="logoGOV"
            alt="Logo Governo PB"
          />
        </Link>
      </div>

      <!-- Links comentados para referência futura -->
      <!--
      <div class="flex items-center gap-3 sm:gap-4 md:gap-6">
        <Search />
        <a
          href="http://localhost:8001/login"
          class="flex items-center gap-2 px-3 py-1.5 md:px-4 md:py-2 text-white rounded-md hover:text-[#bea55a] transition duration-300 justify-center min-w-[200px] sm:min-w-0"
        >
          <img :src="iconComputer" alt="Acadepol Online" class="w-auto h-8 sm:h-9 md:h-10" />
          <span class="text-base sm:text-lg md:text-xl font-medium uppercase tracking-wide">Portal do Aluno</span>
        </a>

        <a
          href="#"
          class="flex items-center gap-2 px-3 py-1.5 md:px-4 md:py-2 text-white rounded-md hover:text-[#bea55a] transition duration-300 justify-center min-w-[200px] sm:min-w-0"
        >
          <img :src="iconGlobe" alt="EAD" class="w-auto h-8 sm:h-9 md:h-10" />
          <span class="text-base sm:text-lg md:text-xl font-medium uppercase tracking-wide">EaD</span>
        </a>
      </div>
      -->
    </div>
  </div>
</template>
