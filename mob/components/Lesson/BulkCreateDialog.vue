<script setup lang="ts">
import { clone } from 'rambda'

interface BulkItem {
  weekdays: { [key in Weekday]: string }
  cabinets: { [key in Weekday]: Cabinet | null }
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
const bulkDefaults: BulkItem = {
  weekdays: {
    0: '',
    1: '',
    2: '',
    3: '',
    4: '',
    5: '',
    6: '',
  },
  cabinets: {
    0: null,
    1: null,
    2: null,
    3: null,
    4: null,
    5: null,
    6: null,
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
const bulk = ref(clone(bulkDefaults))

const isBulkAdd = computed(() => Object.values(bulk.value.weekdays).some(e => !!e))

function create(groupId: number, y: Year) {
  year.value = y
  lesson.value = clone(modelDefaults)
  lesson.value.group_id = groupId
  bulk.value = clone(bulkDefaults)
  dialog.value = true
}

async function save() {
  if (!isBulkAdd.value) {
    return
  }
  const { data } = await useHttp<LessonListResource[]>(
    `lessons/bulk`,
    {
      method: 'post',
      body: {
        bulk: bulk.value,
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
      <UiLoader v-if="loading" />
      <div
        v-else
        class="dialog-body pt-0"
      >
        <!-- <div class="table mb-6">
          <div v-for="(label, index) in WeekdayLabel" :key="index">
            <div
              class="text-uppercase font-weight-medium" style="width: 50px"
              :class="{
                'opacity-2': !bulk.weekdays[index],
              }"
            >
              {{ label }}
            </div>
            <div style="width: 100px">
              <v-text-field
                v-model="bulk.weekdays[index]"
                v-maska:[timeMask]
                density="compact"
              />
            </div>
            <div />
          </div>
        </div> -->

        <table class="dialog-table weekdays-dialog-table">
          <tbody>
            <tr v-for="(label, i) in WeekdayLabel" :key="i">
              <td :class="{ 'weekdays-dialog-table--empty': !(bulk.weekdays[i] || bulk.cabinets[i]) }">
                {{ label.toUpperCase() }}
              </td>
              <td width="180">
                <v-text-field
                  v-model="bulk.weekdays[i]"
                  v-maska:[timeMask]
                  placeholder="Время"
                />
              </td>
              <td width="180">
                <CabinetSelector
                  v-model="bulk.cabinets[i]"
                  placeholder="Кабинет"
                  :date="bulk.start_date"
                  :date-end="bulk.end_date"
                  :time="bulk.weekdays[i]"
                  :weekday="i"
                  :group-id="lesson.group_id"
                />
              </td>
            </tr>
            <tr>
              <td colspan="3" style="height: 0" />
            </tr>
          </tbody>
        </table>
        <div class="double-input">
          <UiDateInput v-model="bulk.start_date" :year="year" label="Дата от" />
          <UiDateInput v-model="bulk.end_date" :year="year" label="до" />
        </div>
        <div class="double-input">
          <v-text-field
            v-model="lesson.price"
            label="Цена"
            type="number"
            hide-spin-buttons
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
  margin-bottom: 20px;
  .v-select {
    max-height: 51px !important;
    height: 51px !important;
    input {
      top: 14px;
      position: relative;
      padding: 0 !important;
      //&::placeholder {
      //
      //}
    }
  }
  &--empty {
    color: #9e9e9e;
  }
}
</style>
