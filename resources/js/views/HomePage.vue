<template>
  <div class="min-h-screen bg-gray-50">
    <Header />
    <Hero />
    <Products />
    <OurProjects />
    <ReviewsSection />
    <Contact />
    <Footer />
  </div>
</template>

<script setup lang="ts">
import Header from '@/components/Header.vue'
import Hero from '@/components/Hero.vue'
import Products from '@/components/Products.vue'
import OurProjects from '@/components/OurProjects.vue'
import ReviewsSection from '@/components/ReviewsSection.vue'
import Contact from '@/components/Contact.vue'
import Footer from '@/components/Footer.vue'
</script>
