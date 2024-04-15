<script setup lang="ts">
import type { Requests } from "~/utils/models"

const items = ref<Requests>()
const paginator = usePaginator()
// const isLastPage = false

async function loadData() {
  if (paginator.loading) {
    return
  }
  paginator.page++
  paginator.loading = true
  console.log("page", paginator.page)
  const { data } = await useHttp<ApiResponse<Requests>>("requests", {
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

// оставлю как пример
// parameter destruction + type declaration
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
  <div v-if="items" class="requests">
    <v-infinite-scroll :onLoad="onIntersect" :margin="100" color="gray">
      <RequestItem v-for="item in items" :item="item" :key="item.id" />
    </v-infinite-scroll>
  </div>
</template>

<style lang="scss">
.requests {
  background: #fafafa;
}
</style>
