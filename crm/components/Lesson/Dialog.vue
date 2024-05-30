<script setup lang="ts">
import { clone } from 'rambda'

const modelDefaults: LessonResource = {
  status: 'planned',
  conducted_at: null,
  is_topic_verified: false,
  is_unplanned: false,
}
const timeMask = { mask: '##:##' }
const { dialog, width } = useDialog('default')
const loading = ref(false)
const lessonId = ref<number | undefined>()
const lesson = ref<LessonResource>(clone(modelDefaults))
const startAt = reactive({
  date: '',
  time: '',
})

const emit = defineEmits<{
  (e: 'created' | 'updated', r: LessonResource): void
}>()

function openDialog(r: LessonResource) {
  [startAt.date, startAt.time] = r.start_at ? r.start_at.split(' ') : ['', '']
  lesson.value = clone(r)
  dialog.value = true
}

function create() {
  lessonId.value = undefined
  openDialog(modelDefaults)
}

async function edit(l: LessonListResource) {
  lessonId.value = l.id
  loading.value = true
  try {
    const { data } = await useHttp<LessonResource>(`lessons/${l.id}`)
    if (data.value) {
      openDialog(data.value)
    }
  }
  catch (error) {
    console.error('Failed to fetch lesson data:', error)
  }
  finally {
    loading.value = false
  }
}

async function save() {
  dialog.value = false
  loading.value = true
  lesson.value.start_at = [startAt.date, startAt.time].join(' ')
  try {
    const method = lessonId.value ? 'put' : 'post'
    const url = lessonId.value ? `lessons/${lessonId.value}` : 'lessons'
    const { data } = await useHttp<LessonResource>(url, {
      method,
      body: lesson.value,
    })
    if (data.value) {
      const event = lessonId.value ? 'updated' : 'created'
      emit(event, data.value)
    }
  }
  catch (error) {
    console.error('Failed to save lesson:', error)
  }
  finally {
    loading.value = false
  }
}

defineExpose({ create, edit })
</script>

<template>
  <v-dialog
    v-model="dialog"
    :width="width"
  >
    <div
      v-if="lesson"
      class="dialog-wrapper"
    >
      <div class="dialog-header">
        <template v-if="lessonId">
          Редактирование урока
        </template>
        <template v-else>
          Добавить урок
        </template>
        <v-btn
          icon="$save"
          :size="48"
          color="#fafafa"
          @click="save()"
        />
      </div>
      <UiLoaderr v-if="loading" />
      <div
        v-else
        class="dialog-body"
      >
        <div>
          <v-select
            v-model="lesson.status"
            label="Статус"
            :items="selectItems(LessonStatusLabel)"
            :disabled="lesson.conducted_at !== null"
          />
        </div>
        <div>
          <TeacherSelector
            v-model="lesson.teacher_id"
            label="Преподаватель"
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
        <div>
          <UiClearableSelect
            v-model="lesson.cabinet"
            :items="Cabinets.map(e => ({
              value: e,
              title: e,
            }))"
            label="Кабинет"
          />
        </div>
        <div class="double-input">
          <UiDateInput v-model="startAt.date" />
          <div>
            <v-text-field
              v-model="startAt.time"
              v-maska:[timeMask]
              label="Время"
            />
          </div>
        </div>
        <div>
          <v-textarea
            v-model="lesson.topic"
            label="Тема"
          />
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
