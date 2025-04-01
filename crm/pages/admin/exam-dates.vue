<script setup lang="ts">
import type { ExamDateDialog } from '#components'

const { items } = useIndex<ExamDateResource>(`exam-dates`)
const examDateDialog = ref<InstanceType<typeof ExamDateDialog>>()

function onSaved(item: ExamDateResource) {
  const index = items.value.findIndex(e => e.id === item.id)
  if (item.dates.length !== items.value[index].dates.length) {
    itemUpdated('exam-date', item.id)
  }
  items.value.splice(index, 1, item)
}
</script>

<template>
  <ExamDateList :items="items" @edit="examDateDialog?.edit" />
  <ExamDateDialog ref="examDateDialog" @saved="onSaved" />
</template>
