<script setup lang="ts">
import type { ContractVersionDialog } from '#build/components'
import type { Filters } from '~/components/ContractVersion/Filters.vue'

const items = ref<ContractVersionListResource[]>([])
const contractVersionDialog = ref<InstanceType<typeof ContractVersionDialog>>()
const loading = ref(false)
const filters = ref<Filters>({
  year: currentAcademicYear(),
})
const total = ref<number>()
let page = 0
let isLastPage = false
let scrollContainer: HTMLElement | null = null

async function loadData() {
  if (loading.value || isLastPage) {
    return
  }
  page++
  loading.value = true
  const { data } = await useHttp<ApiResponse<ContractVersionListResource[]>>(
    'contract-versions',
    {
      params: {
        page,
        ...filters.value,
      },
    },
  )
  if (data.value) {
    const { meta, data: newItems } = data.value
    items.value = page === 1 ? newItems : items.value.concat(newItems)
    isLastPage = meta.current_page >= meta.last_page
    total.value = meta.total
  }
  loading.value = false
}

function onUpdated(cv: ContractVersionListResource) {
  const index = items.value.findIndex(e => e.id === cv.id)
  if (index !== -1) {
    items.value[index] = cv
    itemUpdated('contract-version', cv.id)
  }
}

function onFiltersApply(f: Filters) {
  filters.value = f
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
    <ContractVersionFilters @apply="onFiltersApply" />
    <v-fade-transition>
      <div v-if="total !== undefined" style="flex: 1" class="text-gray">
        всего:
        {{ formatPrice(total) }}
      </div>
    </v-fade-transition>
  </div>
  <div>
    <UiLoader3 :loading="loading" />
    <ContractVersionList
      :items="items"
      @edit="contractVersionDialog?.edit"
    />
  </div>
  <ContractVersionDialog
    ref="contractVersionDialog"
    @updated="onUpdated"
  />
</template>
