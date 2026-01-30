<script lang="ts" setup>
import type { PrintDialog } from '#components'
import type { TeacherActListResource, TeacherActResource } from '.'
import type { PrintOption } from '../Print'
import { apiUrl, modelDefaults } from '.'

const items = defineModel<TeacherActListResource[]>({ required: true })
const printDialog = ref<InstanceType<typeof PrintDialog>>()

const { item, expose, dialog, dialogData } = useCrud<TeacherActResource, TeacherActListResource>(
  apiUrl,
  modelDefaults,
  items,
)

function onPrint() {
  const printOption: PrintOption = {
    id: 23,
    label: 'Акт оказанных услуг',
    company: 'ano',
  }

  printDialog.value?.open(printOption, {
    teacher_ids: [item.value.teacher_id],
    year: item.value.year,
    data: item.value.data,
    date: item.value.date,
    date_from: item.value.date_from,
    date_to: item.value.date_to,
  }, true)
}

defineExpose(expose)
</script>

<template>
  <CrudDialog v-model="dialog" :data="dialogData">
    <template #title-create>
      Добавить договор
    </template>
    <template #title-edit>
      Акт оказанных услуг
    </template>
    <template #buttons>
      <v-btn :size="48" icon="$print" variant="text" @click="onPrint()" />
    </template>
    <div>
      <UiYearSelector v-model="item.year" :disabled="item.id > 0" />
    </div>
    <div>
      <UiDateInput v-model="item.date" today-btn :disabled="item.id > 0" label="Дата в договоре" />
    </div>
    <div class="double-input">
      <UiDateInput v-model="item.date_from" clearable :disabled="item.id > 0" label="Занятия от" />
      <UiDateInput v-model="item.date_to" clearable :disabled="item.id > 0" label="Занятия до" />
    </div>
    <div>
      <FileUploader v-model="item.file" folder="teacher-contracts" class="mt-0" label="прикрепить PDF" />
    </div>
    <div>
      <TeacherContractDataTable :items="item.data" />
    </div>
  </CrudDialog>
  <LazyPrintDialog ref="printDialog" />
</template>
