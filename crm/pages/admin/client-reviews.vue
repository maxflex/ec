<script setup lang="ts">
import type { ClientReviewFilters } from '~/components/ClientReview/Filters.vue'

const filters = ref<ClientReviewFilters>(loadFilters({ }))
const { items, indexPageData } = useIndex<ClientReviewListResource, ClientReviewFilters>(
  `client-reviews`,
  filters,
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <ClientReviewFilters v-model="filters" />
    </template>
    <ClientReviewList :items="items" />
  </UiIndexPage>
</template>
