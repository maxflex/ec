<script setup lang="ts">
const { year, past } = defineProps<{
  year: Year | undefined
  // далёкие даты в прошлом. нужно для установки даты рождения
  past?: boolean
}>()
const emit = defineEmits(['close'])
const { dialog, width, transition } = useDialog('small')
const model = defineModel<string | string[]>({ required: true })

// начиная с какой даты возможен выбор в календаре
// для выбора даты рождения клиента нужны старые даты
const startYear = past ? 2000 : 2015

const years = year === undefined
  ? Array.from({ length: currentAcademicYear() - startYear + 2 }, (_, i) => startYear + i)
  : [year - 1, year, year + 1]

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
  const date = getDate(y, m, d)
  return Array.isArray(model.value)
    ? model.value.includes(date)
    : model.value === date
}

function onClick(y: number, m: number, d: number) {
  const date = getDate(y, m, d)
  if (Array.isArray(model.value)) {
    const index = model.value.findIndex(d => d === date)
    index === -1
      ? model.value.push(date)
      : model.value.splice(index, 1)
  }
  else {
    model.value = date
    dialog.value = false
  }
}

function iterateMonths(y: number): Month[] {
  if (year === undefined) {
    return [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12]
  }
  return y === year
    ? [9, 10, 11, 12]
    : [1, 2, 3, 4, 5, 6, 7, 8]
}

watch(dialog, isOpen => !isOpen && emit('close'))

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
        <div class="calendar">
          <div
            v-for="m in iterateMonths(y)"
            :key="m"
            class="calendar__month"
          >
            <div class="calendar__month-label">
              <span class="text-grey-light">
                {{ MonthLabel[m] }}
                '{{ y - 2000 > 10 ? '' : '0' }}{{ y - 2000 }}
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
