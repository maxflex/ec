<script setup lang="ts">
const { items, loading } = useIndex<InstructionTeacherListResource>(
  `instructions`,
)
</script>

<template>
  <div>
    <UiLoader3 :loading="loading" />
    <InstructionTeacherList :items="items" />
  </div>
</template>
