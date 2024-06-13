<script setup lang="ts">
const items = ref<ClientReviewResource[]>([])
const paginator = usePaginator()

const loadData = async function () {
  if (paginator.loading) {
    return
  }
  paginator.page++
  paginator.loading = true
  const { data } = await useHttp<ApiResponse<ClientReviewResource[]>>('client-reviews', {
    params: {
      'page': paginator.page,
      'with[]': ['client', 'teacher'],
    },
  })
  paginator.loading = false
  if (data.value) {
    const { meta, data: newItems } = data.value
    for (const item of newItems) {
      items.value.push(item)
    }
    // items.value = [...items.value, ...newItems]
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
