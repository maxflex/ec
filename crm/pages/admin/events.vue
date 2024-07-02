<script setup lang="ts">
import type { EventDialog } from '#build/components'
import type { Filters } from '~/components/Event/Filters.vue'

const loading = ref(false)
const items = ref<EventResource[]>([])
let filters: Filters = {
  year: currentAcademicYear(),
}
const eventDialog = ref<InstanceType<typeof EventDialog>>()
let page = 0
let isLastPage = false
let scrollContainer: HTMLElement | null = null

async function loadData() {
  if (loading.value || isLastPage) {
    return
  }
  page++
  loading.value = true
  const { data } = await useHttp<ApiResponse<EventResource[]>>(
    'events',
    {
      params: {
        page,
        ...filters,
      },
    },
  )
  if (data.value) {
    const { meta, data: newItems } = data.value
    items.value = page === 1 ? newItems : items.value.concat(newItems)
    isLastPage = meta.current_page >= meta.last_page
  }
  loading.value = false
}

function onFiltersApply(f: Filters) {
  filters = f
  page = 0
  isLastPage = false
  loadData()
}

function onScroll() {
  if (!scrollContainer || loading.value) {
    return
  }
  const { scrollTop, scrollHeight, clientHeight } = scrollContainer
  const scrollPosition = scrollTop + clientHeight
  const scrollThreshold = scrollHeight * 0.9

  if (scrollPosition >= scrollThreshold) {
    loadData()
  }
}

onMounted(() => {
  scrollContainer = document.documentElement.querySelector('main')
  scrollContainer?.addEventListener('scroll', onScroll)
})

onUnmounted(() => {
  scrollContainer?.removeEventListener('scroll', onScroll)
})

nextTick(loadData)
</script>

<template>
  <div class="filters">
    <EventFilters :filters="filters" @apply="onFiltersApply" />
    <v-btn
      append-icon="$next"
      color="primary"
      @click="eventDialog?.create()"
    >
      добавить событие
    </v-btn>
  </div>
  <div>
    <UiLoader3 :loading="loading" />
    <EventList :items="items" />
  </div>
  <EventDialog ref="eventDialog" />
</template>
