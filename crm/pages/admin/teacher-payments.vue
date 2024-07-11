<script setup lang="ts">
import type { Filters } from '~/components/TeacherPayment/Filters.vue'

const filters = ref<Filters>({
  year: currentAcademicYear(),
})
const items = ref([])
const paginator = usePaginator()
// const isLastPage = false

const loadData = async function () {
  console.log('loading data')
  paginator.page++
  paginator.loading = true
  const { data } = await useHttp<ApiResponse<[]>>('teacher-payments', {
    params: { page: paginator.page, ...filters.value },
  })
  paginator.loading = false
  if (data.value) {
    const { meta, data: newItems } = data.value
    items.value
      = paginator.page === 1 ? newItems : items.value?.concat(newItems)
    paginator.isLastPage = meta.current_page === meta.last_page
  }
}

function onFiltersApply(f: Filters) {
  filters.value = f
  paginator.page = 0
  loadData()
}

async function onIntersect({
  done,
}: {
  done: (status: InfiniteScrollStatus) => void
}) {
  done('loading')
  await loadData()
  done('ok')
}

nextTick(loadData)
</script>

<template>
  <div class="filters">
    <TeacherPaymentFilters @apply="onFiltersApply" />
  </div>
  <UiLoader :paginator="paginator" />
  <v-infinite-scroll
    v-if="items"
    :margin="100"
    side="end"
    @load="onIntersect"
  >
    <TeacherPaymentList :items="items" />
  </v-infinite-scroll>
</template>
