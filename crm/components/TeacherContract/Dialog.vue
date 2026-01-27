<script lang="ts" setup>
import type { PrintDialog } from '#components'
import type { TeacherContractData, TeacherContractListResource, TeacherContractResource } from '.'
import type { PrintOption } from '../Print'
import { mdiAlertBox, mdiArrowDownThin } from '@mdi/js'
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
    if (item.value.problems_count) {
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

// Хелпер для создания уникальной подписи группы (строка вида "price-lessons|price-lessons")
function getGroupSignature(rows: TeacherContractData | undefined) {
  if (!rows || rows.length === 0)
    return ''

  return rows
    .map(r => ({ ...r, price: Number(r.price), lessons: Number(r.lessons) })) // Нормализация типов
    .sort((a, b) => a.price - b.price || a.lessons - b.lessons) // Сортировка для надежности
    .map(r => `${r.price}-${r.lessons}`)
    .join('|')
}

// Вычисляем список ID проблемных групп
const problemGroupIds = computed(() => {
  // Если нет актуальных данных для сравнения, возвращаем пустой список
  if (!actualData.value)
    return []

  const saved = item.value.data || []
  const actual = actualData.value

  // Собираем все уникальные ID групп из обоих списков
  const allGroupIds = new Set([
    ...saved.map(i => i.group_id),
    ...actual.map(i => i.group_id),
  ])

  const diffIds: number[] = []

  allGroupIds.forEach((id) => {
    // Фильтруем строки конкретной группы
    const savedRows = saved.filter(r => r.group_id === id)
    const actualRows = actual.filter(r => r.group_id === id)

    // Сравниваем "отпечатки"
    if (getGroupSignature(savedRows) !== getGroupSignature(actualRows)) {
      diffIds.push(id)
    }
  })

  return diffIds
})

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
      <PrintBtn
        v-if="item.id > 0"
        :items="[21, 22]"
        :extra="{
          teacher_id: item.teacher_id,
          year: item.year,
          data: item.data,
          date: item.date,
        }"
      />
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
      <div v-else-if="item.data">
        <div v-if="item.problems_count" class="text-error d-flex align-center ga-1 pb-10">
          <v-icon :icon="mdiAlertBox" />
          Есть {{ plural(item.problems_count, ['несоответствие', 'несоответствия', 'несоответствий']) }}. В соглашении:
        </div>
        <TeacherContractDataTable :items="item.data" :highlight-ids="problemGroupIds" />
      </div>
    </v-fade-transition>
    <v-fade-transition>
      <div v-if="actualData && item.problems_count">
        <div class="text-error pb-10">
          Актуальные данные:
        </div>
        <TeacherContractDataTable v-if="actualData" :items="actualData" :highlight-ids="problemGroupIds" />
      </div>
    </v-fade-transition>
  </CrudDialog>
  <LazyPrintDialog ref="printDialog" />
</template>
