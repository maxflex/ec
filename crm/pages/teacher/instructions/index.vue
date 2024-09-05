<script setup lang="ts">
const { items, indexPageData } = useIndex<InstructionTeacherListResource>(
  `instructions`,
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <InstructionTeacherList :items="items" />
  </UiIndexPage>
</template>
