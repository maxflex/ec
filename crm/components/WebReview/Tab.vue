<script lang="ts" setup>
import type { WebReviewDialog } from '#components'

const { clientId } = defineProps<{ clientId: number }>()
const items = ref<WebReviewResource[]>([])
const loading = ref(true)
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

const noData = computed(() => !loading.value && items.value.length === 0)

nextTick(loadData)
</script>

<template>
  <UiIndexPage :data="{ loading, noData }">
    <template #buttons>
      <v-btn color="primary" @click="() => webReviewDialog?.create(clientId)">
        добавить
      </v-btn>
    </template>
    <WebReviewList :items="items" @edit="webReviewDialog?.edit" />
  </UiIndexPage>
  <WebReviewDialog ref="webReviewDialog" @updated="onUpdated" />
</template>
