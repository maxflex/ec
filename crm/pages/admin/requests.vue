<script setup lang="ts">
import type { RequestDialog, RequestFiltersDialog } from '#build/components'
import type { Requests } from '~/utils/models'

const items = ref<Requests>()
const paginator = usePaginator()
const requestDialog = ref<null | InstanceType<typeof RequestDialog>>()
const filtersDialog = ref<null | InstanceType<typeof RequestFiltersDialog>>()
const filters = ref<RequestFilters>({})

async function loadData() {
  if (paginator.loading) {
    return
  }
  paginator.page++
  paginator.loading = true
  console.log('page', paginator.page)
  const { data } = await useHttp<ApiResponse<Requests>>('requests', {
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
    <!-- <v-btn icon="$filters" :size="48">
    </v-btn> -->
    <a
      class="cursor-pointer"
      :class="{
        'filters--has-items': Object.values(filters).filter(
          (e) => e !== undefined,
        ).length,
      }"
      @click="filtersDialog?.open()"
    >
      фильтры
    </a>
    <a
      class="cursor-pointer"
      @click="
        requestDialog?.open({
          status: 'new',
        })
      "
    >
      добавить заявку
    </a>
  </div>
  <RequestFiltersDialog
    ref="filtersDialog"
    @apply="onFiltersApply"
  />
  <UiLoader :paginator="paginator" />
  <div
    v-if="items"
    class="requests"
  >
    <v-infinite-scroll
      :on-load="onIntersect"
      :margin="100"
      color="gray"
      :side="'end'"
    >
      <RequestList :requests="items" />
    </v-infinite-scroll>
  </div>
  <RequestDialog ref="requestDialog" />
</template>
