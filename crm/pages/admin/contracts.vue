<script setup lang="ts">
import type { Contracts } from '~/utils/models'
import { COMPANY_TYPE } from '~/utils/sment'

const items = ref<Contracts>()
const paginator = usePaginator()

const loadData = async function () {
  paginator.page++
  paginator.loading = true
  const { data } = await useHttp<ApiResponse<Contracts>>('contracts', {
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
    @load="onIntersect"
    :margin="100"
    class="table"
    side="end"
  >
    <div
      v-for="item in items"
      :key="item.id"
    >
      <div style="width: 50px">
        {{ item.id }}
      </div>
      <div style="width: 350px">
        <NuxtLink :to="{ name: 'clients-id', params: { id: item.client.id } }">
          {{ formatName(item.client) }}
        </NuxtLink>
      </div>
      <div style="width: 300px">
        {{ formatYear(item.year) }}
      </div>
      <div>
        {{ COMPANY_TYPE[item.company] }}
      </div>
    </div>
  </v-infinite-scroll>
</template>
