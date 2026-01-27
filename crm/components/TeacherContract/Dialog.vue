<script lang="ts" setup>
import type { PrintDialog } from '#components'
import type { TeacherContractData, TeacherContractListResource, TeacherContractResource } from '.'
import type { PrintOption } from '../Print'
import { mdiAlertBox } from '@mdi/js'
import { apiUrl, modelDefaults } from '.'

const emit = defineEmits(['updated'])
const items = defineModel<TeacherContractListResource[]>({ required: true })
const printDialog = ref<InstanceType<typeof PrintDialog>>()
// актуальные данные (отображаются в случае, если есть проблемы)
const actualData = ref<TeacherContractData>()

const { item, expose, dialog, dialogData } = useCrud<TeacherContractResource, TeacherContractListResource>(
  apiUrl,
  modelDefaults,
  items,
  {
    afterOpen,
    afterSave: () => emit('updated'),
  },
)

async function afterOpen() {
  actualData.value = undefined

  if (item.value.id > 0) {
    if (item.value.has_problems) {
      actualData.value = await loadData()
    }
    return
  }

  await updateData()
}

async function loadData() {
  const { data } = await useHttp<TeacherContractData>(
    `${apiUrl}/load-data`,
    {
      method: 'POST',
      body: {
        teacher_id: item.value.teacher_id,
        year: item.value.year,
        date_from: item.value.date_from,
        date_to: item.value.date_to,
      },
    },
  )
  return data.value!
}

async function updateData() {
  if (item.value.id > 0) {
    return
  }
  item.value.data = null
  item.value.data = await loadData()
}

function onPrint() {
  const printOption: PrintOption = {
    id: 21,
    label: 'Договор на преподавателя',
    company: 'ano',
  }

  printDialog.value?.open(printOption, {
    teacher_id: item.value.teacher_id,
    year: item.value.year,
    data: item.value.data,
    date: item.value.date,
  }, true)
}

watch(() => item.value.year, async () => {
  item.value = {
    ...item.value,
    date_from: null,
    date_to: null,
  }
  await updateData()
})
watch(() => item.value.date_from, updateData)
watch(() => item.value.date_to, updateData)

defineExpose(expose)
</script>

<template>
  <CrudDialog v-model="dialog" :data="dialogData">
    <template #title-create>
      Добавить договор
    </template>
    <template #title-edit>
      Редактировать договор
    </template>
    <template #buttons>
      <v-btn icon="$print" variant="text" :size="48" @click="onPrint()" />
    </template>
    <div class="double-input">
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
    <v-fade-transition>
      <div v-if="item.data && !item.data.length" class="relative" style="display: inline-block; height: 300px">
        <UiNoData />
      </div>
      <TeacherContractDataTable v-else-if="item.data" :items="item.data" />
    </v-fade-transition>

    <div>
      <v-alert v-if="item.has_problems" :icon="mdiAlertBox" color="error" variant="tonal">
        есть несоответствия с актуальными данными
      </v-alert>
    </div>
    <v-fade-transition>
      <TeacherContractDataTable v-if="actualData" :items="actualData" />
    </v-fade-transition>
  </CrudDialog>
  <LazyPrintDialog ref="printDialog" />
</template>
