<script setup lang="ts">
const emit = defineEmits<{ (e: 'updated'): void }>()

interface BatchItem {
  cabinet?: Cabinet
  quarter?: Quarter
  time?: string
  teacher_id?: number
  price?: number
}

const saving = ref(false)
const timeMask = { mask: '##:##' }
const { dialog, width } = useDialog('default')
const lesson = ref<BatchItem>({})
const deleting = ref(false)
const year = ref<Year>()
const ids = ref<number[]>([])

function open(lessonIds: number[], y: Year) {
  ids.value = lessonIds
  year.value = y
  lesson.value = {}
  dialog.value = true
}

async function save() {
  saving.value = true
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

        <div>
          <v-text-field
            v-model="lesson.time"
            v-maska:[timeMask]
            label="Время"
          />
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
