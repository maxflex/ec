<script setup lang="ts">
const { items } = useIndex<ExamDateResource>(`exam-dates`)
const examDateDialog = ref()

function onSaved(item: ExamDateResource) {
  const index = items.value.findIndex(e => e.id === item.id)
  items.value.splice(index, 1, item)
  itemUpdated('exam-date', item.id)
}
</script>

<template>
  <ExamDateList :items="items" @edit="examDateDialog?.edit" />
  <ExamDateDialog ref="examDateDialog" @saved="onSaved" />
</template>
