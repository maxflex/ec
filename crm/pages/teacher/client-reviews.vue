<script setup lang="ts">
import type { ClientReviewListResource } from '~/components/ClientReview'

const { items, indexPageData } = useIndex<ClientReviewListResource>(
  `client-reviews`,
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <ClientReviewList :items="items" />
  </UiIndexPage>
</template>
