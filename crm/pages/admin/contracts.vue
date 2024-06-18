<script setup lang="ts">
import type { ContractVersionDialog } from '#build/components'
import type { Filters } from '~/components/ContractVersion/Filters.vue'

const items = ref<ContractVersionListResource[]>([])
const paginator = usePaginator()
const key = ref(0)
const contractVersionDialog = ref<InstanceType<typeof ContractVersionDialog>>()
let filters: Filters = loadFilters({})

const loadData = async function () {
  if (paginator.loading) {
    return
  }
  paginator.page++
  paginator.loading = true
  const { data } = await useHttp<ApiResponse<ContractVersionListResource[]>>(
    'contract-versions', {
      params: {
        ...filters,
        page: paginator.page,
      },
    },
  )
  paginator.loading = false
  if (data.value) {
    const { meta, data: newItems } = data.value
    if (paginator.page === 1) {
      items.value = []
      key.value++
    }
    for (const item of newItems) {
      items.value.push(item)
    }
    // items.value = [...items.value, ...newItems]
    paginator.isLastPage = meta.current_page === meta.last_page
  }
}

function onUpdated(cv: ContractVersionListResource) {
  const index = items.value.findIndex(e => e.id === cv.id)
  if (index !== -1) {
    items.value[index] = cv
    itemUpdated('contract-version', cv.id)
  }
}

async function onIntersect({
  done,
}: {
  done: (status: InfiniteScrollStatus) => void
}) {
  if (paginator.isLastPage) {
    return
  }
  done('loading')
  await loadData()
  done('ok')
}

function onFiltersApply(f: Filters) {
  filters = f
  paginator.page = 0
  loadData()
}

nextTick(loadData)
</script>

<template>
  <div class="filters">
    <ContractVersionFilters @apply="onFiltersApply" />
  </div>
  <UiLoader :paginator="paginator" />
  <v-infinite-scroll
    :margin="100"
    @load="onIntersect"
  >
    <ContractVersionList
      :items="items"
      @edit="contractVersionDialog?.edit"
    />
  </v-infinite-scroll>
  <ContractVersionDialog
    ref="contractVersionDialog"
    @updated="onUpdated"
  />
</template>
