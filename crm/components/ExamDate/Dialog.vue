<script setup lang="ts">
import { CalendarDialog } from '#components'

const emit = defineEmits<{
  saved: [item: ExamDateResource]
}>()

const calendarDialog = ref<InstanceType<typeof CalendarDialog>>()
const dates = ref<string[]>([])
let itemId: number

function edit(ed: ExamDateResource) {
  const { id, dates: _dates } = ed
  itemId = id
  dates.value = _dates.slice()
  calendarDialog.value?.open()
}

async function save() {
  const { data } = await useHttp<ExamDateResource>(
      `common/exam-dates/${itemId}`,
      {
        method: 'put',
        body: {
          dates: dates.value,
        },
      },
  )
  if (data.value) {
    emit('saved', data.value)
  }
}

defineExpose({ edit })
</script>

<template>
  <CalendarDialog ref="calendarDialog" v-model="dates" @close="save" />
</template>
