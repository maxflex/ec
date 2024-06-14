<script setup lang="ts">
import type { Filters } from '~/components/Teacher/Filters.vue'

const items = ref<TeacherListResource[]>()
const paginator = usePaginator()
let filters = loadFilters<Filters>({ subjects: [] })

// const isLastPage = false

const loadData = async function () {
  paginator.page++
  paginator.loading = true
  const { data } = await useHttp<ApiResponse<TeacherListResource[]>>('teachers', {
    params: {
      ...filtersToQuery(filters),
      page: paginator.page,
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
    <TeacherFilters @apply="onFiltersApply" />
  </div>
  <UiLoader :paginator="paginator" />
  <v-infinite-scroll
    v-if="items"
    :margin="100"
    class="table"
    side="end"
    @load="onIntersect"
  >
    <div
      v-for="item in items"
      :key="item.id"
    >
      <div style="width: 50px">
        {{ item.id }}
      </div>
      <div style="width: 350px">
        <NuxtLink :to="{ name: 'teachers-id', params: { id: item.id } }">
          {{ formatFullName(item) }}
        </NuxtLink>
      </div>
      <div style="width: 250px">
        {{ TeacherStatusLabel[item.status] }}
      </div>
      <div>
        {{ item.subjects.map(s => SubjectLabelShort[s]).join('+') }}
      </div>
      <!-- <div class="text-right text-gray">
        {{ formatDateTime(item.created_at) }}
      </div> -->
    </div>
  </v-infinite-scroll>
</template>
