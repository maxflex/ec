<script setup lang="ts">
import { cloneDeep } from 'lodash-es'
import { apiUrl, type HeadTeacherReportResource, modelDefaults } from '.'

const emit = defineEmits<{
  updated: [item: HeadTeacherReportResource]
  deleted: [item: HeadTeacherReportResource]
}>()
const { dialog, width } = useDialog('medium')
const saving = ref(false)

const item = ref<HeadTeacherReportResource>({ ...modelDefaults })

const isEditMode = computed(() => item.value.id > 0)

const months = [10, 11, 12, 1, 2, 3, 4, 5].map(m => ({
  title: [
    MonthLabel[m as Month],
    item.value.year + (m <= 5 ? 1 : 0),
  ].join(' '),
  value: m,
}))

function edit(headTeacherReport: HeadTeacherReportResource) {
  item.value = cloneDeep(headTeacherReport)
  dialog.value = true
}

async function save() {
  saving.value = true
  const { data } = isEditMode.value
    ? await useHttp<HeadTeacherReportResource>(
      `${apiUrl}/${item.value.id}`,
      {
        method: 'put',
        body: { ...item.value },
      },
    )
    : await useHttp<HeadTeacherReportResource>(
      apiUrl,
      {
        method: 'post',
        body: { ...item.value },
      },
    )
  dialog.value = false
  saving.value = false
  emit('updated', data.value!)
}

async function onDeleted() {
  emit('deleted', item.value)
  dialog.value = false
}

function create() {
  item.value = cloneDeep(modelDefaults)
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
          <DialogDeleteBtn
            :id="item.id"
            :api-url="apiUrl"
            confirm-text="Вы уверены, что хотите удалить отчёт?"
            @deleted="onDeleted"
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
          <v-select v-model="item.month" :items="months" label="Месяц" />
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
