<script setup lang="ts">
// import type { ClientReviewFilters } from '#build/components'
// type Filters = EmitType<InstanceType<typeof ClientReviewFilters>['$emit']>

import type { Filters } from '~/components/ClientReview/Filters.vue'

const items = ref<ClientReviewResource[]>([])
const paginator = usePaginator()
let filters: Filters = {}
const key = ref(0)

const loadData = async function () {
  if (paginator.loading) {
    return
  }
  paginator.page++
  paginator.loading = true
  const { data } = await useHttp<ApiResponse<ClientReviewResource[]>>('client-reviews', {
    params: {
      ...filters,
      'page': paginator.page,
      'with[]': ['client', 'teacher'],
    },
  })
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

function onFiltersApply(f: Filters) {
  filters = f
  paginator.page = 0
  loadData()
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
  done(paginator.isLastPage ? 'empty' : 'ok')
}

nextTick(loadData)
</script>

<template>
  <div class="filters">
    <ClientReviewFilters @apply="onFiltersApply" />
  </div>
  <UiLoader :paginator="paginator" />
  <v-infinite-scroll
    v-if="items"
    :margin="100"
    side="end"
    @load="onIntersect"
  >
    <ClientReviewList
      :key="key"
      :items="items"
    />
  </v-infinite-scroll>
</template>
