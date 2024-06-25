<script setup lang="ts">
import type { ReportDialog } from '#build/components'
import type { Filters } from '~/components/Report/Filters.vue'

const items = ref<ReportListResource[]>([])
const reportDialog = ref<InstanceType<typeof ReportDialog>>()
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
  const { data } = await useHttp<ApiResponse<ReportListResource[]>>('reports', {
    params: {
      page,
      ...filters.value,
    },
  })
  if (data.value) {
    const { meta, data: newItems } = data.value
    items.value = page === 1 ? newItems : items.value.concat(newItems)
    isLastPage = meta.current_page >= meta.last_page
  }
  loading.value = false
}

function onFiltersApply(f: Filters) {
  filters.value = f
  page = 0
  isLastPage = false
  loadData()
}

function onUpdated(r: ReportListResource) {
  const index = items.value.findIndex(e => e.id === r.id)
  if (index !== -1) {
    items.value[index] = r
  }
  else {
    items.value.unshift(r)
  }
  itemUpdated('report', r.id)
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
    <ReportFilters @apply="onFiltersApply" />
  </div>
  <div>
    <UiLoader3 :loading="loading" />
    <ReportList :items="items" editable @edit="r => reportDialog?.edit(r.id)" />
  </div>
  <ReportDialog
    ref="reportDialog"
    @updated="onUpdated"
  />
</template>
