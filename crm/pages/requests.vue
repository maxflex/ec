<script setup lang="ts">
import type { Requests } from "~/utils/models"

const items = ref<Requests>()
const paginator = usePaginator()
// const isLastPage = false

const loadData = async function () {
  paginator.page++
  paginator.loading = true
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

onMounted(async () => {
  // этот баг только при ssr: true
  // https://github.com/vuejs/core/issues/6638
  // https://github.com/nuxt/nuxt/issues/25131
  // await nextTick()

  await loadData()
})
</script>
<template>
  <div class="requests">
    <RequestItem v-for="item in items" :item="item" />
  </div>
  <div class="text-center my-12">
    <v-btn
      @click="loadData()"
      :loading="paginator.loading"
      size="large"
      elevation="0"
      >показать ещё</v-btn
    >
  </div>
</template>

<style lang="scss">
.requests {
  background: #fafafa;
}
</style>
