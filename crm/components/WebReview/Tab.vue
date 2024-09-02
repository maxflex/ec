<script lang="ts" setup>
import type { WebReviewDialog } from '#components'

const { clientId } = defineProps<{
  clientId: number
}>()
const items = ref<WebReviewResource[]>([])
const loading = ref(false)
const webReviewDialog = ref<InstanceType<typeof WebReviewDialog>>()

async function loadData() {
  loading.value = true
  const { data } = await useHttp<ApiResponse<WebReviewResource[]>>(
      `web-reviews`,
      {
        params: {
          client_id: clientId,
        },
      },
  )
  if (data.value) {
    items.value = data.value.data
  }
  loading.value = false
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

nextTick(loadData)
</script>

<template>
  <div class="filters">
    <div class="filters-inputs" />
    <v-btn color="primary" @click="() => webReviewDialog?.create(clientId)">
      добавить
    </v-btn>
  </div>
  <UiLoaderr v-if="loading" />
  <WebReviewList v-else :items="items" @edit="webReviewDialog?.edit" />
  <WebReviewDialog ref="webReviewDialog" @updated="onUpdated" />
</template>
