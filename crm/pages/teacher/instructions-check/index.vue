<script setup lang="ts">
const { items, indexPageData } = useIndex<InstructionListResource>(
  `instructions-check`,
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <InstructionList :items="items" />
  </UiIndexPage>
</template>
