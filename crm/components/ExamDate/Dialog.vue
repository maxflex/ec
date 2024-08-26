<script setup lang="ts">
import { clone } from 'rambda'

const emit = defineEmits<{
  saved: [item: ExamDateResource]
}>()
const { width, dialog } = useDialog('small')
const item = ref<ExamDateResource>({
  id: newId(),
  exam: 'egeChem',
  dates: [],
})

const years = Object.keys(YearLabel).map(y => Number(y))
// const months = [9, 10, 11, 12, 1, 2, 3, 4, 5, 6]
const months = 12
const saving = ref(false)

function edit(ed: ExamDateResource) {
  item.value = clone(ed)
  dialog.value = true
  setTimeout(() => {
    const selectedElement = document.querySelector('.calendar--selected-clickable')
    const todayElement = document.querySelector('.calendar--today')
    if (selectedElement) {
      selectedElement.scrollIntoView({ block: 'center' })
    }
    else if (todayElement) {
      todayElement.scrollIntoView({ block: 'center' })
    }
  }, 100)
}

// отступ первого дня в календаре
function firstDayOfWeek(y: number, m: number) {
  return new Date(y, m - 1, 0).getDay()
}

function daysInMonth(y: number, m: number) {
  return new Date(y, m, 0).getDate()
}

function zeroPad(value: number): string {
  return (`0${value}`).slice(-2)
}

function getDate(y: number, m: number, d: number): string {
  return [y, zeroPad(m), zeroPad(d)].join('-')
}

function isToday(y: number, m: number, d: number) {
  return getDate(y, m, d) === today()
}

function isSelected(y: number, m: number, d: number) {
  const date = getDate(y, m, d)
  return item.value.dates.includes(date)
}

function onDateClick(y: number, m: number, d: number) {
  const date = getDate(y, m, d)
  const index = item.value.dates.findIndex(e => e === date)
  index === -1
    ? item.value.dates.push(date)
    : item.value.dates.splice(index, 1)
}

async function save() {
  saving.value = true
  const { data } = await useHttp<ExamDateResource>(`exam-dates/${item.value.id}`, {
    method: 'put',
    body: {
      dates: item.value.dates,
    },
  })
  if (data.value) {
    emit('saved', data.value)
  }
  dialog.value = false
  setTimeout(() => saving.value = false, 300)
}

defineExpose({ edit })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-wrapper">
      <div class="dialog-header">
        <div class="capitalize">
          {{ ExamLabel[item.exam] }}
        </div>
        <v-btn
          :size="48"
          icon="$save"
          variant="text"
          :loading="saving"
          @click="save()"
        />
      </div>
      <div class="dialog-body pa-0">
        <!--        <div class="calendar__header" /> -->
        <div
          v-for="y in years"
          :key="y"
          class="calendar__year"
        >
          <h2>{{ y }}</h2>
          <div class="calendar mt-0">
            <div v-for="m in months" :key="m" class="calendar__month">
              <div class="calendar__month-label">
                <span class="text-grey-light">
                  {{ MonthLabel[m - 1] }}
                </span>
              </div>
              <div class="calendar__month-days">
                <div
                  v-for="x in firstDayOfWeek(y, m)"
                  :key="`x${x}`"
                  class="no-pointer-events"
                />
                <div
                  v-for="d in daysInMonth(y, m)"
                  :key="d"
                  :class="{
                    'calendar--selected-clickable': isSelected(y, m, d),
                    'calendar--today': isToday(y, m, d),
                  }"
                  @click="onDateClick(y, m, d)"
                >
                  {{ d }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </v-dialog>
</template>
