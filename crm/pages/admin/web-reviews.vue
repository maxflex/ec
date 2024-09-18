<script setup lang="ts">
import type { WebReviewDialog } from '#components'

const webReviewDialog = ref<InstanceType<typeof WebReviewDialog>>()
const filters = ref<WebReviewFilters>({})
const { items, indexPageData } = useIndex<WebReviewResource, WebReviewFilters>(
    `web-reviews`,
    filters,
)

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
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <WebReviewFilters v-model="filters" />
    </template>
    <WebReviewList :items="items" @edit="webReviewDialog?.edit" />
  </UiIndexPage>
  <WebReviewDialog ref="webReviewDialog" @updated="onUpdated" />
</template>
