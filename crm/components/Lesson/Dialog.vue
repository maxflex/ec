<script setup lang="ts">
import { clone } from 'rambda'

interface BatchAdd {
  weekdays: { [key: number]: string }
  start_date: string
  end_date: string
}

const emit = defineEmits<{
  updated: [l: LessonListResource]
  destroyed: [l: LessonListResource]
  batchSaved: [l: LessonListResource[]]
}>()

const modelDefaults: LessonResource = {
  id: newId(),
  status: 'planned',
  conducted_at: null,
  is_topic_verified: false,
  is_unplanned: false,
}
const dayLabels = ['пн', 'вт', 'ср', 'чт', 'пт', 'сб', 'вс'] as const
const batchDefaults: BatchAdd = {
  weekdays: {
    0: '',
    1: '',
    2: '',
    3: '',
    4: '',
    5: '',
    6: '',
  },
  start_date: '',
  end_date: '',
}
const saving = ref(false)
const timeMask = { mask: '##:##' }
const { dialog, width } = useDialog('default')
const loading = ref(false)
const itemId = ref<number | undefined>()
const lesson = ref<LessonResource>(clone(modelDefaults))
const deleting = ref(false)
const startAt = reactive({
  date: '',
  time: '',
})
const year = ref<Year>()

// групповое добавление
const batch = ref(clone(batchDefaults))

const isBatchAdd = computed(() => Object.values(batch.value.weekdays).some(e => !!e))

function create(groupId: number, y: Year) {
  itemId.value = undefined
  year.value = y
  lesson.value = clone(modelDefaults)
  lesson.value.group_id = groupId
  batch.value = clone(batchDefaults)
  dialog.value = true
}

async function edit(lessonId: number) {
  itemId.value = lessonId
  loading.value = true
  dialog.value = true
  const { data } = await useHttp<LessonResource>(`lessons/${lessonId}`)
  if (data.value) {
    lesson.value = data.value
    year.value = getAcademicYear(lesson.value.start_at!)
  }
  loading.value = false
}

watch(lesson, () => {
  [startAt.date, startAt.time] = lesson.value.start_at
    ? lesson.value.start_at.split(' ')
    : ['', '']
})

async function save() {
  saving.value = true
  // групповое добавление
  if (isBatchAdd.value) {
    return saveBatch()
  }
  lesson.value.start_at = [startAt.date, startAt.time].join(' ')
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

async function saveBatch() {
  const { data } = await useHttp<LessonListResource[]>(
    `lessons/batch`,
    {
      method: 'post',
      body: {
        batch: batch.value,
        lesson: lesson.value,
      },
    },
  )
  if (data.value) {
    emit('batchSaved', data.value)
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
        <template v-if="itemId">
          Редактирование занятия
        </template>
        <template v-else>
          Добавить занятие
        </template>
        <v-btn
          icon="$save"
          :size="48"
          color="#fafafa"
          :loading="saving"
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
            :items="selectItems(CabinetLabel)"
            label="Кабинет"
          />
        </div>
        <div class="double-input">
          <UiDateInput
            v-model="startAt.date"
            :year="year"
            :disabled="isBatchAdd"
          />
          <div>
            <v-text-field
              v-model="startAt.time"
              v-maska:[timeMask]
              label="Время"
              :disabled="isBatchAdd"
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
        <div v-if="!itemId" class="dialog-section">
          <div class="dialog-section__title">
            Групповое добавление
          </div>
          <div class="table mt-4 mb-10">
            <div v-for="(label, index) in dayLabels" :key="index">
              <div
                class="text-uppercase" style="width: 50px"
                :class="{
                  'opacity-2': !batch.weekdays[index],
                }"
              >
                {{ label }}
              </div>
              <div style="width: 100px">
                <v-text-field
                  v-model="batch.weekdays[index]"
                  v-maska:[timeMask]
                  density="compact"
                />
              </div>
              <div />
            </div>
          </div>
          <div class="double-input">
            <UiDateInput v-model="batch.start_date" :year="year" label="Дата от" />
            <UiDateInput v-model="batch.end_date" :year="year" label="до" />
          </div>
        </div>
        <div v-else class="dialog-bottom">
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
            :loading="deleting"
            @click="destroy()"
          />
        </div>
      </div>
    </div>
  </v-dialog>
</template>
