<template>
  <div class="min-h-screen bg-gray-50">
    <Header />
    <main class="pt-20">
      <OurProjects />
    </main>
    <Footer />
  </div>
</template>

<script setup lang="ts">
import Header from '@/components/Header.vue'
import Footer from '@/components/Footer.vue'
import OurProjects from '@/components/OurProjects.vue'
</script>
