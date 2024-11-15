<script setup lang="ts">
const { teacherId } = defineProps<{ teacherId: number }>()

const { items, indexPageData } = useIndex<ClientListResource>(`clients`, ref({}), {
  staticFilters: {
    head_teacher_id: teacherId,
  },
})
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <ClientList :items="items" />
  </UiIndexPage>
</template>
