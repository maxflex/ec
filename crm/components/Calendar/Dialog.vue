<script setup lang="ts">
const { year } = defineProps<{
  year?: Year
}>()
const { dialog, width, transition } = useDialog('small')
const model = defineModel<string>()

const years = year === undefined
  ? Object.keys(YearLabel).map(y => Number(y))
  : [year, year + 1]

function open() {
  dialog.value = true
  setTimeout(() => {
    const selectedElement = document.querySelector('.calendar--selected')
    const todayElement = document.querySelector('.calendar--today')

    if (selectedElement) {
      selectedElement.scrollIntoView({ block: 'center' })
    }
    else if (todayElement) {
      todayElement.scrollIntoView({ block: 'center' })
    }
  }, 100)
}

function zeroPad(value: number): string {
  return (`0${value}`).slice(-2)
}

// отступ первого дня в календаре
function firstDayOfWeek(y: number, m: number) {
  return new Date(y, m - 1, 0).getDay()
}

function daysInMonth(y: number, m: number) {
  return new Date(y, m, 0).getDate()
}

function getDate(y: number, m: number, d: number): string {
  return [y, zeroPad(m), zeroPad(d)].join('-')
}

function isToday(y: number, m: number, d: number) {
  return getDate(y, m, d) === today()
}

function isSelected(y: number, m: number, d: number) {
  return getDate(y, m, d) === model.value
}

function onClick(y: number, m: number, d: number) {
  console.log('onClick', y, m, d)
  model.value = getDate(y, m, d)
  dialog.value = false
}

function iterateMonths(y: number) {
  if (year === undefined) {
    return 12
  }
  return y === year
    ? [9, 10, 11, 12]
    : [1, 2, 3, 4, 5, 6, 7, 8]
}

defineExpose({ open })
</script>

<template>
  <v-dialog
    v-model="dialog"
    :width="width"
    :transition="transition"
  >
    <v-card class="calendar-card">
      <div class="calendar__header">
        <!-- <v-btn icon @click="dialog = false" variant="flat" :size="48">
          <v-icon icon="$close"></v-icon>
        </v-btn> -->
      </div>
      <div
        v-for="y in years"
        :key="y"
        class="calendar__year"
      >
        <h2>{{ y }}</h2>
        <div class="calendar">
          <div
            v-for="m in iterateMonths(y)"
            :key="m"
            class="calendar__month"
          >
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
                  'calendar--today': isToday(y, m, d),
                  'calendar--selected': isSelected(y, m, d),
                }"
                @click="onClick(y, m, d)"
              >
                {{ d }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </v-card>
  </v-dialog>
</template>
