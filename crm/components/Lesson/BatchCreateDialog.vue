<script setup lang="ts">
import { clone } from 'rambda'

interface BatchItem {
  weekdays: { [key in Weekday]: string }
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
          variant="text"
          :loading="saving"
          @click="save()"
        />
      </div>
      <UiLoaderr v-if="loading" />
      <div
        v-else
        class="dialog-body pt-0"
      >
        <!-- <div class="table mb-6">
          <div v-for="(label, index) in WeekdayLabel" :key="index">
            <div
              class="text-uppercase font-weight-medium" style="width: 50px"
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
        </div> -->
        <table class="dialog-table weekdays-dialog-table">
          <tbody>
            <tr v-for="(arr, index) in [[0, 4], [1, 5], [2, 6], [3, -1]]" :key="index">
              <td width="60" class="text-uppercase">
                {{ WeekdayLabel[arr[0]] }}
              </td>
              <td>
                <v-text-field
                  v-model="batch.weekdays[arr[0]]"
                  v-maska:[timeMask]
                />
              </td>
              <td width="63" class="text-uppercase">
                <template v-if="arr[1] !== -1">
                  {{ WeekdayLabel[arr[1]] }}
                </template>
              </td>
              <td :class="{ 'no-pointer-events': arr[1] === -1 }">
                <v-text-field
                  v-model="batch.weekdays[arr[1]]"
                  v-maska:[timeMask]
                />
              </td>
            </tr>
            <tr>
              <td style="height: 20px !important" colspan="2" />
            </tr>
          </tbody>
        </table>
        <div class="double-input">
          <UiDateInput v-model="batch.start_date" :year="year" label="Дата от" />
          <UiDateInput v-model="batch.end_date" :year="year" label="до" />
        </div>
        <div class="double-input">
          <UiClearableSelect
            v-model="lesson.cabinet"
            :items="selectItems(CabinetLabel)"
            label="Кабинет"
          />
          <UiClearableSelect
            v-model="lesson.quarter"
            :items="selectItems(QuarterLabel, ['final' as Quarter])"
            label="Четверть"
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
      </div>
    </div>
  </v-dialog>
</template>

<style lang="scss">
.weekays-inputs {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  row-gap: 20px;
  column-gap: 20px;
}
.weekdays-dialog-table {
  tr {
    td {
      &:nth-child(1),
      &:nth-child(3) {
        border-right: none !important;
        color: rgb(var(--v-theme-gray));
        text-transform: uppercase;
      }
      &:nth-child(3) {
        padding-left: 16px;
      }
    }
  }
}
</style>
