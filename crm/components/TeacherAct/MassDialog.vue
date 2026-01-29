<script lang="ts" setup>
import type { PrintDialog } from '#components'
import type { TeacherActData, TeacherActResource } from '.'
import type { PrintOption } from '../Print'
import { cloneDeep } from 'lodash-es'
import { apiUrl, modelDefaults } from '.'

const emit = defineEmits(['saved'])

const { dialog, width } = useDialog(600)

const item = ref<TeacherActResource>({ ...modelDefaults })
const actData = ref<TeacherActData>()
const teacherIds = ref<number[]>([])
const saving = ref(false)
const printDialog = ref<InstanceType<typeof PrintDialog>>()
function open() {
  item.value = cloneDeep(modelDefaults)
  dialog.value = true
  updateData()
}

async function save() {
  if (!teacherIds.value.length) {
    useGlobalMessage('Выберите преподавателей', 'error')
    return
  }
  saving.value = true
  await useHttp(`${apiUrl}/mass-store`, {
    method: 'POST',
    body: getPostBody(),
  })
  saving.value = false
  dialog.value = false
  useGlobalMessage(`Создано ${plural(teacherIds.value.length, ['акт', 'акта', 'актов'])}`, 'success')
  emit('saved')
}

function onPrint() {
  if (!teacherIds.value.length) {
    useGlobalMessage('Выберите преподавателей', 'error')
    return
  }

  const printOption: PrintOption = {
    id: 23,
    label: 'Акт оказанных услуг',
  }
  printDialog.value?.open(printOption, getPostBody(), true)
}

function getPostBody() {
  return {
    date: item.value.date,
    date_from: item.value.date_from,
    date_to: item.value.date_to,
    year: item.value.year,
    teacher_ids: cloneDeep(teacherIds.value),
  }
}

async function loadData() {
  const { data } = await useHttp<TeacherActData>(
    `${apiUrl}/load-mass-data`,
    {
      method: 'POST',
      body: {
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
  teacherIds.value = []
  actData.value = undefined
  actData.value = await loadData()
}

function toggleSwitch(teacherId: number) {
  const index = teacherIds.value.findIndex(e => e === teacherId)
  index === -1
    ? teacherIds.value.push(teacherId)
    : teacherIds.value.splice(index, 1)
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

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-wrapper">
      <div class="dialog-header">
        Добавить акты
        <div>
          <v-btn icon="$print" variant="text" :size="48" @click="onPrint()" />
          <v-btn icon="$save" variant="text" :size="48" :loading="saving" @click="save()" />
        </div>
      </div>
      <div class="dialog-body">
        <div>
          <UiYearSelector v-model="item.year" />
        </div>
        <div>
          <UiDateInput v-model="item.date" today-btn :disabled="item.id > 0" label="Дата в договоре" />
        </div>
        <div class="double-input">
          <UiDateInput v-model="item.date_from" clearable :disabled="item.id > 0" label="Занятия от" />
          <UiDateInput v-model="item.date_to" clearable :disabled="item.id > 0" label="Занятия до" />
        </div>
        <v-fade-transition>
          <div v-if="actData && !actData.length" class="relative" style="display: inline-block; height: 300px">
            <UiNoData />
          </div>
          <table v-else-if="actData && actData.length" class="dialog-table">
            <thead>
              <tr>
                <th :width="210">
                  преподаватель
                </th>
                <th :width="94">
                  групп
                </th>
                <th :width="94">
                  занятий
                </th>
                <th :width="120">
                  сумма
                </th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="d in actData" :key="d.teacher.id" class="padding">
                <td>
                  <UiPerson class="text-truncate" :item="d.teacher" @click.stop />
                </td>
                <td>
                  {{ d.groups }}
                </td>
                <td>
                  {{ d.lessons }}
                </td>
                <td>
                  {{ formatPrice(d.price) }}
                </td>
                <td>
                  <v-switch
                    :model-value="teacherIds.includes(d.teacher.id)"
                    class="vf-3"
                    @click="toggleSwitch(d.teacher.id)"
                  />
                </td>
              </tr>
              <tr>
                <td :colspan="100" />
              </tr>
            </tbody>
          </table>
        </v-fade-transition>
      </div>
    </div>
  </v-dialog>
  <LazyPrintDialog ref="printDialog" />
</template>
