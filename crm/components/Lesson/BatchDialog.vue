<script setup lang="ts">
const emit = defineEmits<{ (e: 'updated'): void }>()

interface BatchItem {
  cabinet?: Cabinet
  start_at?: string
  teacher_id?: number
  price?: number
}

const saving = ref(false)
const timeMask = { mask: '##:##' }
const { dialog, width } = useDialog('default')
const lesson = ref<BatchItem>({})
const deleting = ref(false)
const startAt = reactive({
  date: '',
  time: '',
})
const year = ref<Year>()
const ids = ref<number[]>([])

function open(lessonIds: number[], y: Year) {
  ids.value = lessonIds
  year.value = y
  lesson.value = {}
  dialog.value = true
}

watch(lesson, () => {
  [startAt.date, startAt.time] = lesson.value.start_at
    ? lesson.value.start_at.split(' ')
    : ['', '']
})

async function save() {
  saving.value = true
  lesson.value.start_at = [startAt.date, startAt.time].join(' ')
  await useHttp(`lessons/batch`, {
    method: 'put',
    body: {
      ids: ids.value,
      lesson: lesson.value,
    },
  })
  emit('updated')
  dialog.value = false
  setTimeout(() => saving.value = false, 300)
}

async function destroy() {
  if (!confirm(`Вы уверены, что хотите удалить ${ids.value.length} уроков?`)) {
    return
  }
  deleting.value = true
  await useHttp(`lessons/batch`, {
    method: 'delete',
    params: {
      'ids[]': ids.value,
    },
  })
  emit('updated')
  dialog.value = false
  setTimeout(() => deleting.value = false, 300)
}

defineExpose({ open })
</script>

<template>
  <v-dialog
    v-model="dialog"
    :width="width"
  >
    <div class="dialog-wrapper">
      <div class="dialog-header">
        <span>
          Массовое редактирование
          <span class="ml-1 text-gray">
            {{ ids.length }}
          </span>
        </span>
        <v-btn
          icon="$save"
          :size="48"
          color="#fafafa"
          :loading="saving"
          @click="save()"
        />
      </div>
      <div class="dialog-body">
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
          />
          <div>
            <v-text-field
              v-model="startAt.time"
              v-maska:[timeMask]
              label="Время"
            />
          </div>
        </div>
        <div class="dialog-bottom">
          <v-spacer />
          <v-btn
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
