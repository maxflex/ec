<script setup lang="ts">
import { cloneDeep } from 'lodash-es'

const emit = defineEmits<{
  updated: [l: LessonListResource]
}>()

const { isAdmin, isTeacher } = useAuthStore()

const modelDefaults: LessonResource = {
  id: newId(),
  status: 'planned',
  conducted_at: null,
  is_topic_verified: false,
  is_unplanned: false,
  is_free: false,
  quarter: null,
  files: [],
}

const saving = ref(false)
const timeMask = { mask: '##:##' }
const { dialog, width } = useDialog('default')
const loading = ref(false)
const itemId = ref<number | undefined>()
const lesson = ref<LessonResource>(cloneDeep(modelDefaults))
const year = ref<Year>()
// если занятие проведено, нельзя менять статус на "отмена"
const isConducted = ref(false)

function create(groupId: number, y: Year) {
  itemId.value = undefined
  year.value = y
  lesson.value = cloneDeep(modelDefaults)
  lesson.value.group_id = groupId
  isConducted.value = false
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
    isConducted.value = lesson.value.status === 'conducted'
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

const uploadingFiles = computed(() => lesson.value.files.some(e => !e.url))

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
          <CrudDeleteBtn
            v-if="itemId && lesson.conducted_at === null"
            :id="itemId"
            api-url="lessons"
            confirm-text="Вы уверены, что хотите удалить занятие?"
            @deleted="dialog = false"
          />
          <v-btn
            icon="$save"
            :size="48"
            variant="text"
            :disabled="uploadingFiles"
            :loading="saving"
            @click="save()"
          />
        </div>
      </div>
      <UiLoader v-if="loading" />
      <div v-else class="dialog-body">
        <div v-if="isAdmin">
          <TeacherSelector
            v-model="lesson.teacher_id"
            label="Преподаватель"
            :disabled="isConducted"
          />
        </div>
        <div v-if="isAdmin">
          <CabinetSelector
            v-model="lesson.cabinet"
            label="Кабинет"
            :date="lesson.date"
            :time="lesson.time"
            :group-id="lesson.group_id!"
            :disabled="isConducted"
          />
        </div>
        <div v-if="isAdmin">
          <UiClearableSelect
            v-model="lesson.quarter"
            :items="selectItems(QuarterLabel, ['q1', 'q2', 'q3', 'q4'])"
            label="Четверть"
            :disabled="isConducted"
            nullify
          />
        </div>
        <div v-if="isAdmin">
          <v-select
            v-model="lesson.status"
            :items="selectItems(LessonStatusLabel).filter(e => isConducted ? true : e.value !== 'conducted')"
            label="Статус"
            :disabled="isConducted"
          />
        </div>
        <div v-if="isAdmin">
          <v-text-field
            v-model="lesson.price"
            label="Цена"
            type="number"
            hide-spin-buttons
            :disabled="isConducted"
          />
        </div>

        <div v-if="isAdmin" class="double-input">
          <UiDateInput
            v-model="lesson.date"
            :year="year"
            :disabled="isConducted"
          />
          <div>
            <v-text-field
              v-model="lesson.time"
              v-maska="timeMask"
              label="Время"
              :disabled="isConducted"
            />
          </div>
        </div>
        <div>
          <v-textarea
            v-model="lesson.topic"
            :disabled="isTeacher && lesson.is_topic_verified"
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
          <FileUploader v-model="lesson.files" folder="lessons" />
        </div>
        <div>
          <v-checkbox
            v-model="lesson.is_topic_verified"
            :disabled="isTeacher"
            label="Тема подтверждена"
          />
          <v-checkbox
            v-model="lesson.is_unplanned"
            :disabled="isTeacher"
            label="Внеплановое"
          />
          <v-checkbox
            v-model="lesson.is_free"
            :disabled="isTeacher"
            label="Бесплатное для детей"
          />
        </div>
      </div>
    </div>
  </v-dialog>
</template>
