<script setup lang="ts">
import type { Contracts } from "~/utils/models"

const items = ref<Contracts>()
const paginator = usePaginator()

const loadData = async function () {
  paginator.page++
  paginator.loading = true
  const { data } = await useHttp<ApiResponse<Contracts>>("contracts", {
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

onMounted(async () => {
  // этот баг только при ssr: true
  // https://github.com/vuejs/core/issues/6638
  // https://github.com/nuxt/nuxt/issues/25131
  // await nextTick()

  await loadData()
})

async function onIntersect({
  done,
}: {
  done: (status: InfiniteScrollStatus) => void
}) {
  done("loading")
  await loadData()
  done("ok")
}
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
      <div style="width: 50px">
        {{ item.id }}
      </div>
      <div style="width: 350px">
        <NuxtLink :to="{ name: 'clients-id', params: { id: item.client.id } }">
          {{ formatName(item.client) }}
        </NuxtLink>
      </div>
      <div style="width: 300px">
        <UiYear :year="item.year" />
      </div>
      <div>
        {{ item.company === "ip" ? "ИП" : "ООО" }}
      </div>
    </div>
  </v-infinite-scroll>
</template>
