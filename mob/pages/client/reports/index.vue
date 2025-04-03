<script setup lang="ts">
const { items, indexPageData } = useIndex<ReportListResource>(`reports`)
</script>

<template>
  <UiPageTitle>
    Отчёты
  </UiPageTitle>
  <UiIndexPage :data="indexPageData">
    <ReportList :items="items" />
  </UiIndexPage>
</template>

<style lang="scss">
.page-reports {
  .table > a:first-child {
    padding-top: 0 !important;
  }
}
</style>
