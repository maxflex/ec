<script setup lang="ts">
const items = ref([])
const paginator = usePaginator()
// const isLastPage = false

const loadData = async function () {
  console.log('loading data')
  paginator.page++
  paginator.loading = true
  const { data } = await useHttp<ApiResponse<[]>>('client-reviews', {
    params: { page: paginator.page },
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
  done(paginator.isLastPage ? 'empty' : 'ok')
}

nextTick(loadData)
</script>

<template>
  <UiLoader :paginator="paginator" />
  <v-infinite-scroll
    v-if="items"
    :margin="100"
    side="end"
    @load="onIntersect"
  >
    <ClientReviewList :items="items" />
  </v-infinite-scroll>
</template>
