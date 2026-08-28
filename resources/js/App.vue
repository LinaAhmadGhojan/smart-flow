<template>
  <div id="app">
    <RouterView />
    <ChatAssistant v-if="showPublicChat" />
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { RouterView, useRoute } from 'vue-router'
import ChatAssistant from '@/components/ChatAssistant.vue'

const route = useRoute()
const showPublicChat = computed(() => !route.path.startsWith('/admin'))
</script>

<style>
#app {
  width: 100%;
  min-height: 100vh;
}
</style>
