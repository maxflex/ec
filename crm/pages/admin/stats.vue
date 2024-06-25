<script setup lang="ts">
import type { Filters } from '~/components/Stats/Filters.vue'

const items = ref<StatsResource[]>()
const paginator = usePaginator()
let filters = loadFilters<Filters>({ mode: 'day' })

// const isLastPage = false

const loadData = async function () {
  paginator.page++
  paginator.loading = true
  const { data } = await useHttp<ApiResponse<StatsResource[]>>('stats', {
    params: {
      ...filtersToQuery(filters),
      page: paginator.page,
      paginate: 30,
    },
  })
  paginator.loading = false
  if (data.value) {
    const { meta, data: newItems } = data.value
    items.value
      = paginator.page === 1 ? newItems : items.value?.concat(newItems)
    paginator.isLastPage = meta.current_page === meta.last_page
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
    <StatsFilters @apply="onFiltersApply" />
  </div>
  <UiLoader :paginator="paginator" />
  <div class="table stats-list stats-list__header" />
  <v-infinite-scroll
    v-if="items"
    :margin="100"
    side="end"
    @load="onIntersect"
  >
    <StatsList
      :items="items"
      :mode="filters.mode"
    />
  </v-infinite-scroll>
</template>
