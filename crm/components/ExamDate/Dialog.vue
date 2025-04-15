<script setup lang="ts">
import { clone } from 'lodash'

const emit = defineEmits<{
  saved: [item: ExamDateResource]
}>()

const dialog = ref(false)
const item = ref<ExamDateResource>({
  id: newId(),
  dates: [],
  programs: [],
  exam: 'egeBio',
})

function edit(ed: ExamDateResource) {
  item.value = clone(ed)
  open()
}

async function save() {
  const { data } = await useHttp<ExamDateResource>(
    `exam-dates/${item.value.id}`,
    {
      method: 'put',
      body: {
        dates: item.value.dates,
      },
    },
  )
  if (data.value) {
    emit('saved', data.value)
  }
}

// calendar controls start

// начиная с какой даты возможен выбор в календаре
const startYear = 2015
const scrolling = ref(true)
const years = Array.from({ length: currentAcademicYear() - startYear + 4 }, (_, i) => startYear + i)

function open() {
  dialog.value = true
  scrollToActiveDate()
}

function scrollToActiveDate() {
  scrolling.value = true
  nextTick(() => {
    const selectedElement = document.querySelector('.calendar--selected')
    const todayElement = document.querySelector('.calendar--today')

    if (selectedElement) {
      selectedElement.scrollIntoView({ block: 'center' })
    }
    else if (todayElement) {
      todayElement.scrollIntoView({ block: 'center' })
    }
    scrolling.value = false
  })
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

function findByDate(y: number, m: number, d: number) {
  const date = getDate(y, m, d)
  return item.value.dates.find(d => d.date === date)
}

function onClick(y: number, m: number, d: number) {
  const date = getDate(y, m, d)
  const index = item.value.dates.findIndex(d => d.date === date)
  if (index === -1) {
    item.value.dates.push({
      date,
      is_reserve: 0,
    })
  }
  else {
    if (item.value.dates[index].is_reserve === 1) {
      item.value.dates.splice(index, 1)
    }
    else {
      item.value.dates.splice(index, 1, {
        date,
        is_reserve: 1,
      })
    }
  }
}

// canendar controls end

watch(dialog, (isOpen) => {
  if (!isOpen) {
    save()
  }
})

defineExpose({ edit })
</script>

<template>
  <v-dialog v-model="dialog" class="dialog-fullwidth">
    <v-fade-transition>
      <UiLoader v-if="scrolling" style="z-index: 1" />
    </v-fade-transition>
    <v-card class="calendar-card">
      <div class="calendar-dialog__header">
        <v-btn icon variant="flat" :size="48" color="primary" @click="dialog = false">
          <v-icon icon="$close"></v-icon>
        </v-btn>
      </div>
      <div
        v-for="y in years"
        :key="y"
        class="calendar__year"
      >
        <h2>{{ y }}</h2>
        <div class="calendar">
          <div
            v-for="m in 12"
            :key="m"
            class="calendar__month"
          >
            <div class="calendar__month-label">
              <span class="text-grey-light">
                {{ MonthLabel[m as Month] }}
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
                  'calendar--selected': findByDate(y, m, d)?.is_reserve === 0,
                  'calendar--selected-accent': findByDate(y, m, d)?.is_reserve === 1,
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
