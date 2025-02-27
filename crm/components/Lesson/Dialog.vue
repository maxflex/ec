<script setup lang="ts">
import { clone } from 'rambda'

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

interface BusyCabinet {
  cabinet: Cabinet
  is_busy: boolean
}

function getAllCabinets(): BusyCabinet[] {
  return Object.keys(CabinetLabel).map(id => ({
    cabinet: id as Cabinet,
    is_busy: false,
  }))
}

const saving = ref(false)
const timeMask = { mask: '##:##' }
const { dialog, width } = useDialog('default')
const loading = ref(false)
const itemId = ref<number | undefined>()
const lesson = ref<LessonResource>(clone(modelDefaults))
const year = ref<Year>()
// если занятие проведено, нельзя менять статус на "отмена"
const isConducted = ref(false)
const cabinets = ref<BusyCabinet[]>(getAllCabinets())
const watchers = []

function create(groupId: number, y: Year) {
  cabinets.value = getAllCabinets()
  itemId.value = undefined
  year.value = y
  lesson.value = clone(modelDefaults)
  lesson.value.group_id = groupId
  dialog.value = true
  watchers.push(
    watch(() => lesson.value.date, loadFreeCabinets),
  )
  watchers.push(
    watch(() => lesson.value.time, loadFreeCabinets),
  )
}

async function edit(lessonId: number) {
  cabinets.value = getAllCabinets()
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

// при установке даты и времени, загружаем свободные кабинеты
async function loadFreeCabinets() {
  if (lesson.value.date && lesson.value.time && lesson.value.time.length === 5) {
    const { data } = await useHttp<BusyCabinet[]>(`cabinets/free`, {
      params: {
        group_id: lesson.value.group_id,
        date: lesson.value.date,
        time: lesson.value.time,
      },
    })
    cabinets.value = data.value!
  }
  else {
    cabinets.value = getAllCabinets()
  }
}

watch(dialog, (isOpen) => {
  if (!isOpen) {
    watchers.forEach(unwatch => unwatch())
  }
})

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
          <DialogDeleteBtn
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
            :items="cabinets"
            :disabled="isConducted"
          />
        </div>
        <div v-if="isAdmin">
          <UiClearableSelect
            v-model="lesson.quarter"
            :items="selectItems(QuarterLabel, ['final' as Quarter])"
            label="Четверть"
            :disabled="isConducted"
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
              v-maska:[timeMask]
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
