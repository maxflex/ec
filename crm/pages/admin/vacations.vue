<script setup lang="ts">
const monthLabels = [
  'январь',
  'февраль',
  'март',
  'апрель',
  'май',
  'июнь',
  'июль',
  'август',
  'сентябрь',
  'октябрь',
  'ноябрь',
  'декабрь',
]
const years = Object.keys(YearLabel).map(y => Number(y))
years.push(years[years.length - 1] + 1) // добавляем слеюующий год
years.push(years[years.length - 1] + 1) // добавляем слеюующий год
const loading = ref(true)
const dates = ref<Record<string, boolean>>({})

onMounted(async () => {
  setTimeout(
    () =>
      document
        ?.querySelector('.calendar--today')
        ?.scrollIntoView({ block: 'center' }),
    100,
  )
})

async function loadData() {
  const { data } = await useHttp<ApiResponse<VacationResource>>(`vacations`)
  if (data.value) {
    // vacations.value = data.value.data
    for (const { date } of data.value.data) {
      dates.value[date] = true
    }
  }
  setTimeout(() => (loading.value = false), 300)
}

const zeroPad = (value: number) => (`0${value}`).slice(-2)

// отступ первого дня в календаре
function firstDayOfWeek(year: number, month: number) {
  return new Date(year, month - 1, 0).getDay()
}

function daysInMonth(year: number, month: number) {
  return new Date(year, month, 0).getDate()
}

function getDate(year: number, month: number, day: number): string {
  return [year, zeroPad(month), zeroPad(day)].join('-')
}

function isToday(year: number, month: number, day: number) {
  return getDate(year, month, day) === today()
}

function isSelected(year: number, month: number, day: number) {
  return getDate(year, month, day) in dates.value
}

function onClick(year: number, month: number, day: number) {
  const date = getDate(year, month, day)
  if (date in dates.value) {
    delete dates.value[date]
  }
  else {
    dates.value[date] = true
  }
  useHttp(`vacations`, {
    method: 'post',
    body: { date },
  })
  // if (data.value) {
  //   vacations.value?.push(data.value as VacationResource)
  // }
}

defineExpose({ open })
nextTick(loadData)
</script>

<template>
  <v-fade-transition>
    <UiLoader v-if="loading" style="z-index: 1" />
  </v-fade-transition>
  <v-card class="calendar-card">
    <div class="calendar__header">
      <!-- <v-btn icon @click="dialog = false" variant="flat" :size="48">
          <v-icon icon="$close"></v-icon>
        </v-btn> -->
    </div>
    <div v-for="year in years" :key="year" class="calendar__year">
      <h2>{{ year }}</h2>
      <div class="calendar">
        <div v-for="month in 12" :key="month" class="calendar__month">
          <div class="calendar__month-label">
            <span>
              {{ monthLabels[month - 1] }}
            </span>
          </div>
          <div class="calendar__month-days">
            <div v-for="x in firstDayOfWeek(year, month)" :key="`x${x}`" class="no-pointer-events" />
            <div
              v-for="day in daysInMonth(year, month)" :key="day" :class="{
                'calendar--today': isToday(year, month, day),
                'calendar--selected': isSelected(year, month, day),
              }" @click="onClick(year, month, day)"
            >
              {{ day }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </v-card>
</template>
