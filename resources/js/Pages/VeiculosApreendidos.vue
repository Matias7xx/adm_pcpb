<script setup>
import { Head } from '@inertiajs/vue3'
import VeiculosLista from './Components/VeiculosLista.vue'
import Header from './Components/Header.vue'
import SiteNavbar from './Components/SiteNavbar.vue'
import Footer from './Components/Footer.vue'
</script>

<template>
  <Head title="Veículos Apreendidos" />
  
  <div class="min-h-screen flex flex-col bg-gray-100">
    <Header />
    <SiteNavbar />
    
    <VeiculosLista />
    
    <Footer />
  </div>
</template>