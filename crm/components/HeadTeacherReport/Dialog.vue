<script setup lang="ts">
import { clone } from 'rambda'

const emit = defineEmits<{
  updated: [item: HeadTeacherReportResource]
  deleted: [item: HeadTeacherReportResource]
}>()
const { dialog, width } = useDialog('medium')
const modelDefaults: HeadTeacherReportResource = {
  id: newId(),
  text: '',
  month: currentMonth(),
  year: currentAcademicYear(),
}
const saving = ref(false)
const deleting = ref(false)

const item = ref<HeadTeacherReportResource>({ ...modelDefaults })

const isEditMode = computed(() => item.value.id > 0)

function edit(headTeacherReport: HeadTeacherReportResource) {
  item.value = clone(headTeacherReport)
  dialog.value = true
}

async function save() {
  saving.value = true
  const { data } = isEditMode.value
    ? await useHttp<HeadTeacherReportResource>(
        `head-teacher-reports/${item.value.id}`,
        {
          method: 'put',
          body: { ...item.value },
        },
    )
    : await useHttp<HeadTeacherReportResource>(
        `head-teacher-reports`,
        {
          method: 'post',
          body: { ...item.value },
        },
    )
  dialog.value = false
  saving.value = false
  emit('updated', data.value!)
}

async function destroy() {
  if (!confirm('Вы уверены, что хотите удалить отчёт?')) {
    return
  }
  deleting.value = true
  await useHttp(
      `head-teacher-reports/${item.value.id}`,
      {
        method: 'delete',
      },
  )
  emit('deleted', item.value)
  dialog.value = false
  deleting.value = false
}

function create() {
  item.value = clone(modelDefaults)
  dialog.value = true
}

defineExpose({ edit, create })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-wrapper">
      <div class="dialog-header">
        <div v-if="isEditMode">
          Редактирование отчёта
          <div class="dialog-subheader">
            {{ formatDateTime(item.created_at!) }}
          </div>
        </div>
        <template v-else>
          Создать отчёт
        </template>
        <div>
          <v-btn
            v-if="isEditMode"
            icon="$delete"
            :size="48"
            variant="text"
            :loading="deleting"
            class="remove-btn"
            @click="destroy()"
          />
          <v-btn
            icon="$save"
            :size="48"
            variant="text"
            :loading="saving"
            @click="save()"
          />
        </div>
      </div>
      <div class="dialog-body">
        <div class="double-input">
          <v-select v-model="item.month" :items="selectItems(MonthLabel)" label="Месяц" />
          <UiYearSelector v-model="item.year" disabled />
        </div>
        <div>
          <v-textarea
            v-model="item.text"
            rows="10"
            no-resize
            auto-grow
            label="Текст отчёта"
          />
        </div>
      </div>
    </div>
  </v-dialog>
</template>
