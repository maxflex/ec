<script setup lang="ts">
import type { WebReviewDialog } from '#components'
import type { Filters } from '~/components/WebReview/Filters.vue'

const { items, loading, onFiltersApply } = useIndex<WebReviewResource, Filters>(`web-reviews`)

const webReviewDialog = ref<InstanceType<typeof WebReviewDialog>>()

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
  <UiFilters>
    <WebReviewFilters @apply="onFiltersApply" />
  </UiFilters>

  <div>
    <UiLoader3 :loading="loading" />
    <WebReviewList :items="items" @edit="webReviewDialog?.edit" />
  </div>
  <WebReviewDialog ref="webReviewDialog" @updated="onUpdated" />
</template>
