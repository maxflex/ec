<script setup lang="ts">
import type { ClientReviewFilters } from '~/components/ClientReview/Filters.vue'

const filters = ref<ClientReviewFilters>(loadFilters({ }))
const { items, indexPageData } = useIndex<ClientReviewListResource, ClientReviewFilters>(
  `client-reviews`,
  filters,
)
</script>

<template>
  <UiIndexPage :data="indexPageData" class="client-reviews">
    <template #filters>
      <ClientReviewFilters v-model="filters" />
    </template>
    <ClientReviewList :items="items" />
  </UiIndexPage>
</template>

<style lang="scss">
.page-client-reviews {
  .filters {
    max-width: 100vw !important;
    &__inputs {
      width: 100%;
      & > div {
        flex: 1;
        width: auto !important;
      }
    }
  }
}
</style>
