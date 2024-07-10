<script setup lang="ts">
import { clone } from 'rambda'

interface BatchItem {
  weekdays: { [key: number]: string }
  start_date: string
  end_date: string
}

const emit = defineEmits<{
  updated: [l: LessonListResource[]]
}>()

const modelDefaults = {
  teacher_id: null,
  cabinet: null,
  quarter: null,
  price: null,
  group_id: 0,
}
const dayLabels = ['пн', 'вт', 'ср', 'чт', 'пт', 'сб', 'вс'] as const
const batchDefaults: BatchItem = {
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
const lesson = ref(clone(modelDefaults))
const year = ref<Year>()

// групповое добавление
const batch = ref(clone(batchDefaults))

const isBatchAdd = computed(() => Object.values(batch.value.weekdays).some(e => !!e))

function create(groupId: number, y: Year) {
  year.value = y
  lesson.value = clone(modelDefaults)
  lesson.value.group_id = groupId
  batch.value = clone(batchDefaults)
  dialog.value = true
}

async function save() {
  if (!isBatchAdd.value) {
    return
  }
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
    emit('updated', data.value)
  }
  dialog.value = false
  setTimeout(() => saving.value = false, 300)
}

defineExpose({ create })
</script>

<template>
  <v-dialog
    v-model="dialog"
    :width="width"
  >
    <div class="dialog-wrapper">
      <div class="dialog-header">
        Добавить несколько занятий
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
        class="dialog-body pt-0"
      >
        <div class="table mb-6">
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
      </div>
    </div>
  </v-dialog>
</template>
