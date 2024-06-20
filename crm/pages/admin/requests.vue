<script setup lang="ts">
import type { RequestDialog } from '#build/components'
import type { Filters } from '~/components/Request/Filters.vue'

const items = ref<RequestListResource[]>([])
const requestDialog = ref<InstanceType<typeof RequestDialog>>()
const filters = ref<Filters>({})
const loading = ref(false)
let page = 0
let isLastPage = false
let scrollContainer: HTMLElement | null = null

async function loadData() {
  if (loading.value || isLastPage) {
    return
  }
  page++
  loading.value = true
  const { data } = await useHttp<ApiResponse<RequestListResource[]>>('requests', {
    params: {
      page,
      ...filters.value,
    },
  })
  if (data.value) {
    const { meta, data: newItems } = data.value
    items.value = items.value.concat(newItems)
    isLastPage = meta.current_page >= meta.last_page
  }
  loading.value = false
}

function onFiltersApply(f: Filters) {
  filters.value = f
  page = 0
  loadData()
}

function onRequestCreated(r: RequestListResource) {
  items.value?.unshift(r)
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
    <RequestFilters @apply="onFiltersApply" />
    <a
      class="cursor-pointer"
      @click="requestDialog?.create()"
    >
      добавить заявку
    </a>
  </div>
  <div>
    <UiLoader3 :loading="loading" />
    <RequestList v-model="items" />
  </div>
  <RequestDialog
    ref="requestDialog"
    @created="onRequestCreated"
  />
</template>
