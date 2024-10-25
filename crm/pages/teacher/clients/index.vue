<script setup lang="ts">
const { items, indexPageData } = useIndex<ClientListResource>(
    `clients`,
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <ClientList :items="items" />
  </UiIndexPage>
</template>
