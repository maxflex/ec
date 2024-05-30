<script setup lang="ts">
import { mdiWeb } from '@mdi/js'

const items = ref([])
const paginator = usePaginator()
// const isLastPage = false

const loadData = async function () {
  console.log('loading data')
  paginator.page++
  paginator.loading = true
  const { data } = await useHttp<ApiResponse<[]>>('web-reviews', {
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
      <div style="width: 200px">
        <NuxtLink :to="{ name: 'clients-id', params: { id: item.client_id } }">
          {{ formatName(item.client) }}
        </NuxtLink>
      </div>
      <div
        style="width: 200px"
        class="text-truncate"
      >
        {{ item.signature }}
      </div>
      <div
        style="flex: 1"
        class="text-truncate"
      >
        {{ item.text }}
      </div>
      <div
        style="width: 50px"
        class="text-center"
      >
        <v-icon
          :class="`web-review--${item.is_published ? 'published' : 'unpublished'}`"
          :icon="mdiWeb"
          :color="item.is_published ? 'secondary' : 'gray'"
        />
      </div>
      <div
        class="text-right"
        style="width: 100px; flex: initial"
      >
        <v-rating
          class="no-pointer-events"
          :model-value="item.rating"
          density="compact"
          size="small"
          color="orange"
        />
      </div>
    </div>
  </v-infinite-scroll>
</template>

<style lang="scss">
.web-review {
  &--unpublished {
    opacity: 0.2;
  }
}
</style>
