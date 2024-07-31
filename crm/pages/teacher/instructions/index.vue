<script setup lang="ts">
const { items, loading } = useIndex<InstructionTeacherResource>(
  `instructions`,
)
</script>

<template>
  <div>
    <UiLoader3 :loading="loading" />
    <InstructionTeacherList :items="items" />
  </div>
</template>
