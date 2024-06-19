<script setup lang="ts">
import type { RequestDialog, RequestFilters } from '#build/components'

const items = ref<RequestListResource[]>()
const paginator = usePaginator()
const requestDialog = ref<InstanceType<typeof RequestDialog>>()
const filters = ref<RequestFilters>({})

async function loadData() {
  if (paginator.loading) {
    return
  }
  paginator.page++
  paginator.loading = true
  console.log('page', paginator.page)
  const { data } = await useHttp<ApiResponse<RequestListResource[]>>('requests', {
    params: {
      page: paginator.page,
      ...filters.value,
    },
  })
  paginator.loading = false
  if (data.value) {
    const { meta, data: newItems } = data.value
    items.value
      = paginator.page === 1 ? newItems : items.value?.concat(newItems)
    paginator.isLastPage = meta.current_page >= meta.last_page
  }
}

function onFiltersApply(f: RequestFilters) {
  filters.value = f
  paginator.page = 0
  loadData()
}

function onRequestCreated(r: RequestListResource) {
  items.value?.unshift(r)
}

// оставлю как пример
// parameter destruction + type declaration
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
    <RequestFilters @apply="onFiltersApply" />
    <a
      class="cursor-pointer"
      @click="requestDialog?.create()"
    >
      добавить заявку
    </a>
  </div>
  <UiLoader :paginator="paginator" />
  <div
    v-if="items"
    class="requests"
  >
    <v-infinite-scroll
      :margin="100"
      color="gray"
      side="end"
      @load="onIntersect"
    >
      <RequestList v-model="items" />
    </v-infinite-scroll>
  </div>
  <RequestDialog
    ref="requestDialog"
    @created="onRequestCreated"
  />
</template>
