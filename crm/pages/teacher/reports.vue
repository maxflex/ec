<script setup lang="ts">
import type { ReportDialog } from '#build/components'
import type { Filters } from '~/components/Report/TeacherFilters.vue'

const items = ref<ReportListResource[]>([])
const reportDialog = ref<InstanceType<typeof ReportDialog>>()
const loading = ref(false)
let page = 0
let isLastPage = false
let scrollContainer: HTMLElement | null = null
const filters = ref<Filters>({
  year: currentAcademicYear(),
})

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

function onUpdated(r: RealReportItem) {
  const index = items.value.findIndex(e => e.id === r.id)
  if (index === -1) {
    return
  }
  items.value[index] = r
  itemUpdated('report', r.id)
}

function onCreated(r: RealReportItem, fakeItemId: string) {
  const index = items.value.findIndex(e => e.id === fakeItemId)
  if (index === -1) {
    return
  }
  items.value[index] = r
  itemUpdated('report', r.id)
}

function onDeleted(r: RealReportItem) {
  const index = items.value.findIndex(e => e.id === r.id)
  if (index !== -1) {
    items.value.splice(index, 1)
  }
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

function onFiltersApply(f: Filters) {
  filters.value = f
  page = 0
  isLastPage = false
  if (scrollContainer) {
    scrollContainer.scrollTop = 0
  }
  loadData()
}

onMounted(() => {
  scrollContainer = document.documentElement.querySelector('main')
  scrollContainer?.addEventListener('scroll', onScroll)
})

onUnmounted(() => {
  scrollContainer?.removeEventListener('scroll', onScroll)
})

watch(filters.value, () => {
  page = 0
  isLastPage = false
  loadData()
})

nextTick(loadData)
</script>

<template>
  <div class="filters">
    <ReportTeacherFilters @apply="onFiltersApply" />
  </div>
  <div>
    <UiLoader3 :loading="loading" />
    <ReportList
      :items="items"
      @edit="r => reportDialog?.edit(r.id)"
      @create="reportDialog?.create"
    />
  </div>
  <ReportDialog
    ref="reportDialog"
    @updated="onUpdated"
    @created="onCreated"
    @deleted="onDeleted"
  />
</template>
