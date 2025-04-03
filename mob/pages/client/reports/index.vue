<script setup lang="ts">
const { items, indexPageData } = useIndex<RealReport>(`reports`)
</script>

<template>
  <UiPageTitle>
    Отчёты
  </UiPageTitle>
  <UiIndexPage :data="indexPageData">
    <ReportList :items="items" />
  </UiIndexPage>
</template>
