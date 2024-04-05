<script setup lang="ts">
import type { Clients } from "~/utils/models"

const items = ref<Clients>()
const paginator = usePaginator()
// const isLastPage = false

const loadData = async function () {
  console.log("loading data")
  paginator.page++
  paginator.loading = true
  const { data } = await useHttp<ApiResponse<Clients>>("clients", {
    params: { page: paginator.page },
  })
  paginator.loading = false
  if (data.value) {
    const { meta, data: newItems } = data.value
    items.value =
      paginator.page === 1 ? newItems : items.value?.concat(newItems)
    paginator.isLastPage = meta.current_page === meta.last_page
  }
}

async function onIntersect({
  done,
}: {
  done: (status: InfiniteScrollStatus) => void
}) {
  done("loading")
  await loadData()
  done("ok")
}

nextTick(loadData)
</script>

<template>
  <UiLoader :paginator="paginator" />
  <v-infinite-scroll
    :onLoad="onIntersect"
    :margin="100"
    class="table"
    side="end"
    v-if="items"
  >
    <div v-for="item in items" :key="item.id">
      <div width="50">
        {{ item.id }}
      </div>
      <div>
        <NuxtLink :to="{ name: 'clients-id', params: { id: item.id } }">
          {{ formatName(item) }}
        </NuxtLink>
      </div>
      <div class="text-right text-gray">
        {{ formatDateTime(item.created_at) }}
      </div>
    </div>
  </v-infinite-scroll>
</template>
