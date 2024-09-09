<script setup lang="ts">
import { clone } from 'rambda'

const emit = defineEmits<{
  updated: [l: LessonListResource]
  destroyed: [l: LessonListResource]
}>()

const modelDefaults: LessonResource = {
  id: newId(),
  status: 'planned',
  conducted_at: null,
  is_topic_verified: false,
  is_unplanned: false,
  quarter: null,
  files: [],
}

const saving = ref(false)
const timeMask = { mask: '##:##' }
const { dialog, width } = useDialog('default')
const loading = ref(false)
const itemId = ref<number | undefined>()
const lesson = ref<LessonResource>(clone(modelDefaults))
const deleting = ref(false)
const year = ref<Year>()

function create(groupId: number, y: Year) {
  itemId.value = undefined
  year.value = y
  lesson.value = clone(modelDefaults)
  lesson.value.group_id = groupId
  dialog.value = true
}

async function edit(lessonId: number) {
  itemId.value = lessonId
  loading.value = true
  dialog.value = true
  const { data } = await useHttp<LessonResource>(`lessons/${lessonId}`)
  if (data.value) {
    lesson.value = data.value
    year.value = getAcademicYear(lesson.value.date!)
  }
  loading.value = false
}

async function save() {
  saving.value = true
  const method = itemId.value ? 'put' : 'post'
  const url = itemId.value ? `lessons/${itemId.value}` : 'lessons'
  const { data } = await useHttp<LessonListResource>(url, {
    method,
    body: lesson.value,
  })
  if (data.value) {
    emit('updated', data.value)
    itemUpdated('lesson', data.value.id)
  }
  dialog.value = false
  setTimeout(() => saving.value = false, 300)
}

async function destroy() {
  if (!confirm('Вы уверены, что хотите удалить урок?')) {
    return
  }
  deleting.value = true
  const { data, status } = await useHttp<LessonListResource>(`lessons/${lesson.value.id}`, {
    method: 'delete',
  })
  if (status.value === 'error') {
    deleting.value = false
  }
  else if (data.value) {
    emit('destroyed', data.value)
    dialog.value = false
    setTimeout(() => deleting.value = false, 300)
  }
}

defineExpose({ create, edit })
</script>

<template>
  <v-dialog
    v-model="dialog"
    :width="width"
  >
    <div class="dialog-wrapper">
      <div class="dialog-header">
        <div v-if="itemId">
          Редактирование занятия
          <div class="dialog-subheader">
            {{ lesson.user ? formatName(lesson.user) : 'неизвестно' }}
            <template v-if="lesson.created_at">
              {{ formatDateTime(lesson.created_at) }}
            </template>
          </div>
        </div>
        <template v-else>
          Добавить занятие
        </template>
        <div>
          <v-btn
            v-if="itemId && lesson.conducted_at === null"
            icon="$delete"
            :size="48"
            class="remove-btn"
            variant="text"
            :loading="deleting"
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
      <UiLoader v-if="loading" />
      <div
        v-else
        class="dialog-body"
      >
        <div>
          <TeacherSelector
            v-model="lesson.teacher_id"
            label="Преподаватель"
          />
        </div>
        <div>
          <UiClearableSelect
            v-model="lesson.cabinet"
            :items="selectItems(CabinetLabel)"
            label="Кабинет"
          />
        </div>
        <div>
          <UiClearableSelect
            v-model="lesson.quarter"
            :items="selectItems(QuarterLabel, ['final' as Quarter])"
            label="Четверть"
          />
        </div>
        <div>
          <v-text-field
            v-model="lesson.price"
            label="Цена"
            type="number"
            hide-spin-buttons
          />
        </div>

        <div class="double-input">
          <UiDateInput
            v-model="lesson.date"
            :year="year"
          />
          <div>
            <v-text-field
              v-model="lesson.time"
              v-maska:[timeMask]
              label="Время"
            />
          </div>
        </div>
        <div>
          <v-textarea
            v-model="lesson.topic"
            label="Тема"
            no-resize
          />
        </div>
        <div>
          <v-textarea
            v-model="lesson.homework"
            label="Домашнее задание"
            no-resize
          />
        </div>
        <div>
          <FileUploader v-model="lesson.files" />
        </div>
        <div>
          <v-checkbox
            v-model="lesson.is_topic_verified"
            label="Тема подтверждена"
          />
          <v-checkbox
            v-model="lesson.is_unplanned"
            label="Внеплановое"
          />
        </div>
      </div>
    </div>
  </v-dialog>
</template>
