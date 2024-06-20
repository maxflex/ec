<script setup lang="ts">
import { clone } from 'rambda'

const emit = defineEmits<{ (e: 'updated' | 'destroyed', r: LessonListResource): void }>()
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
const destroying = ref(false)
const startAt = reactive({
  date: '',
  time: '',
})

function openDialog(l: LessonResource) {
  [startAt.date, startAt.time] = l.start_at ? l.start_at.split(' ') : ['', '']
  lesson.value = clone(l)
  dialog.value = true
}

function create(groupId: number) {
  lessonId.value = undefined
  openDialog({
    ...modelDefaults,
    group_id: groupId,
  })
}

async function edit(l: LessonListResource) {
  lessonId.value = l.id
  loading.value = true
  const { data } = await useHttp<LessonResource>(`lessons/${l.id}`)
  if (data.value) {
    openDialog(data.value)
  }
  loading.value = false
}

async function save() {
  dialog.value = false
  loading.value = true
  lesson.value.start_at = [startAt.date, startAt.time].join(' ')
  const method = lessonId.value ? 'put' : 'post'
  const url = lessonId.value ? `lessons/${lessonId.value}` : 'lessons'
  const { data } = await useHttp<LessonListResource>(url, {
    method,
    body: lesson.value,
  })
  if (data.value) {
    emit('updated', data.value)
  }
  loading.value = false
}

async function destroy() {
  if (!confirm('Вы уверены, что хотите удалить урок?')) {
    return
  }
  destroying.value = true
  const { data, status } = await useHttp<LessonListResource>(`lessons/${lesson.value.id}`, {
    method: 'delete',
  })
  if (status.value === 'error') {
    destroying.value = false
  }
  else if (data.value) {
    emit('destroyed', data.value)
    dialog.value = false
    setTimeout(() => destroying.value = false, 300)
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
            no-resize
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
        <div
          v-if="lessonId"
          class="dialog-bottom"
        >
          <span v-if="lesson.user && lesson.created_at">
            урок создан
            {{ formatName(lesson.user) }}
            {{ formatDateTime(lesson.created_at) }}
          </span>
          <v-btn
            v-if="lesson.conducted_at === null"
            icon="$delete"
            :size="48"
            color="red"
            variant="plain"
            :loading="destroying"
            @click="destroy()"
          />
        </div>
      </div>
    </div>
  </v-dialog>
</template>
