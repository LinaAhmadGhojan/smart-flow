<template>
  <section id="projects" class="py-16 bg-white">
    <div class="container mx-auto px-4">
      <div class="text-center mb-12">
        <h2 class="text-4xl font-bold text-blue-900 mb-1">
          مشاريعنا | Our Projects
        </h2>
        <p class="text-xl text-gray-600">
          أعمالنا المميزة في مختلف المجالات
        </p>
      </div>

      <div v-if="loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>

      <div v-else-if="!projects.length" class="text-center py-12 text-gray-400">
        لا مشاريع معروضة حالياً
      </div>

      <div v-else class="grid gap-8 md:grid-cols-2 xl:grid-cols-3">
        <article
          v-for="project in projects"
          :key="project.id"
          class="group bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-xl transition-shadow"
          :class="project.is_featured ? 'ring-2 ring-blue-500' : ''"
        >
          <div class="relative aspect-[16/10] bg-gray-100 overflow-hidden">
            <img
              v-if="activeMedia(project)?.kind === 'image'"
              :src="mediaUrl(activeMedia(project)!.path)"
              :alt="project.title_ar || project.title"
              class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
              @error="handleMediaError"
            />
            <iframe
              v-else-if="activeMedia(project)?.kind === 'video'"
              :src="getEmbedUrl(activeMedia(project)!.path)"
              class="w-full h-full"
              allowfullscreen
              frameborder="0"
            />
            <div
              v-else
              class="w-full h-full flex items-center justify-center bg-gradient-to-br from-blue-600 to-blue-800"
            >
              <svg class="w-16 h-16 text-white/40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
              </svg>
            </div>

            <span
              v-if="project.is_featured"
              class="absolute top-3 right-3 bg-amber-400 text-amber-950 text-xs font-bold px-3 py-1 rounded-full shadow"
            >
              مميز
            </span>

            <div
              v-if="gallery(project).length > 1"
              class="absolute bottom-3 left-0 right-0 flex justify-center gap-1.5"
            >
              <button
                v-for="(item, idx) in gallery(project)"
                :key="item.id || idx"
                type="button"
                class="h-1.5 rounded-full transition-all"
                :class="(currentIndex[project.id] || 0) === idx ? 'w-6 bg-white' : 'w-1.5 bg-white/50'"
                @click="setIndex(project.id, idx)"
              />
            </div>
          </div>

          <div class="p-5 space-y-3">
            <div>
              <h3 class="text-xl font-bold text-gray-900 leading-snug">
                {{ project.title_ar || project.title }}
              </h3>
              <p v-if="project.title_ar && project.title" class="text-sm text-gray-500 mt-0.5">
                {{ project.title }}
              </p>
            </div>

            <p v-if="project.location" class="text-sm text-blue-700 flex items-center gap-1.5">
              <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              {{ project.location }}
            </p>

            <p class="text-gray-600 text-sm leading-relaxed line-clamp-3">
              {{ project.description_ar || project.description }}
            </p>

            <div v-if="docs(project).length" class="flex flex-wrap gap-2 pt-1">
              <a
                v-for="doc in docs(project)"
                :key="doc.id"
                :href="mediaUrl(doc.path)"
                target="_blank"
                rel="noopener noreferrer"
                class="text-xs px-2.5 py-1 rounded-lg bg-gray-50 border border-gray-200 text-gray-700 hover:bg-blue-50 hover:border-blue-200 hover:text-blue-700 transition-colors"
              >
                {{ doc.label || 'ملف' }}
              </a>
            </div>

            <div class="pt-2 flex items-center justify-between gap-3">
              <div v-if="gallery(project).length > 1" class="flex gap-1">
                <button
                  type="button"
                  class="p-1.5 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50"
                  @click="prev(project)"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
                <button
                  type="button"
                  class="p-1.5 rounded-lg border border-gray-200 text-gray-500 hover:bg-gray-50"
                  @click="next(project)"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>
              </div>
              <a
                :href="`https://wa.me/971562566232?text=${encodeURIComponent('مرحباً، أنا مهتم بمشروع: ' + (project.title_ar || project.title))}`"
                target="_blank"
                rel="noopener noreferrer"
                class="ms-auto bg-green-600 hover:bg-green-700 text-white text-sm px-4 py-2 rounded-lg font-medium inline-flex items-center gap-2"
              >
                استفسر
              </a>
            </div>
          </div>
        </article>
      </div>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { mediaUrl, handleMediaError } from '@/lib/media'

interface ProjectFile {
  id: number
  label: string
  path: string
  kind: 'image' | 'video' | 'document' | string
}

interface Project {
  id: number
  title: string
  title_ar: string
  description: string
  description_ar: string
  location?: string | null
  is_featured: boolean
  cover?: string | null
  media_type?: string
  media_url?: string | null
  files?: ProjectFile[]
}

const projects = ref<Project[]>([])
const loading = ref(true)
const currentIndex = ref<Record<number, number>>({})

const gallery = (p: Project): ProjectFile[] => {
  const media = (p.files || []).filter((f) => f.kind === 'image' || f.kind === 'video')
  if (media.length) return media
  if (p.cover) return [{ id: 0, label: 'غلاف', path: p.cover, kind: 'image' }]
  if (p.media_url) {
    return [{
      id: 0,
      label: 'وسائط',
      path: p.media_url,
      kind: p.media_type === 'video' ? 'video' : 'image',
    }]
  }
  return []
}

const docs = (p: Project) => (p.files || []).filter((f) => f.kind === 'document')

const activeMedia = (p: Project) => {
  const items = gallery(p)
  if (!items.length) return null
  return items[currentIndex.value[p.id] || 0] || items[0]
}

const setIndex = (id: number, idx: number) => {
  currentIndex.value[id] = idx
}

const next = (p: Project) => {
  const len = gallery(p).length
  if (!len) return
  currentIndex.value[p.id] = ((currentIndex.value[p.id] || 0) + 1) % len
}

const prev = (p: Project) => {
  const len = gallery(p).length
  if (!len) return
  currentIndex.value[p.id] = ((currentIndex.value[p.id] || 0) - 1 + len) % len
}

function getEmbedUrl(url: string | null): string {
  if (!url) return ''
  const ytMatch = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/shorts\/)([\w-]{11})/)
  if (ytMatch) return `https://www.youtube.com/embed/${ytMatch[1]}`
  const vimeoMatch = url.match(/vimeo\.com\/(\d+)/)
  if (vimeoMatch) return `https://player.vimeo.com/video/${vimeoMatch[1]}`
  return url
}

onMounted(async () => {
  try {
    const response = await axios.get('/api/projects')
    projects.value = Array.isArray(response.data) ? response.data : []
    projects.value.forEach((p) => { currentIndex.value[p.id] = 0 })
  } catch (error) {
    console.error('Error fetching projects:', error)
  } finally {
    loading.value = false
  }
})
</script>
