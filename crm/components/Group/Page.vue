<script setup lang="ts">
import type { Groups } from '~/utils/models'

const items = ref<Groups>()
const paginator = usePaginator()

const loadData = async function () {
  paginator.page++
  paginator.loading = true
  const { data } = await useHttp<ApiResponse<Groups>>('groups', {
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
    class="table table--padding"
    @load="onIntersect"
  >
    <GroupItem
      v-for="item in items"
      :key="item.id"
      :group="item"
    />
  </v-infinite-scroll>
</template>
