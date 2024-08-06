<script setup lang="ts">
import type { WebReviewDialog } from '#components'

const items = ref<WebReviewResource[]>([])
const paginator = usePaginator()
const webReviewDialog = ref<InstanceType<typeof WebReviewDialog>>()

const loadData = async function () {
  if (paginator.loading) {
    return
  }
  paginator.page++
  paginator.loading = true
  const { data } = await useHttp<ApiResponse<[]>>('web-reviews', {
    params: { page: paginator.page },
  })
  paginator.loading = false
  if (data.value) {
    const { meta, data: newItems } = data.value
    for (const item of newItems) {
      items.value.push(item)
    }
    paginator.isLastPage = meta.current_page === meta.last_page
  }
}

function onUpdated(item: WebReviewResource, deleted: boolean) {
  const index = items.value.findIndex(e => e.id === item.id)
  if (index === -1) {
    items.value.unshift(item)
  }
  else {
    deleted
      ? items.value.splice(index, 1)
      : items.value.splice(index, 1, item)
  }
  itemUpdated('web-review', item.id)
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
    <WebReviewList :items="items" @edit="webReviewDialog?.edit" />
  </v-infinite-scroll>
  <WebReviewDialog ref="webReviewDialog" @updated="onUpdated" />
</template>
