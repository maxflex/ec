<script setup lang="ts">
const { items, indexPageData } = useIndex<ClientReviewListResource>(
  `client-reviews`,
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <ClientReviewList :items="items" />
  </UiIndexPage>
</template>
