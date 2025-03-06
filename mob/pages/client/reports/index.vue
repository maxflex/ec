<script setup lang="ts">
const { items, indexPageData } = useIndex<ReportListResource>(`reports`)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <ReportList :items="items" />
  </UiIndexPage>
</template>
