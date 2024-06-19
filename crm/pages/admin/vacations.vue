<script setup lang="ts">
import type { Vacations } from '~/utils/models'

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
const years = Object.keys(YearLabel).toReversed().map(y => Number(y))
years.push(years[years.length] + 1) // добавляем слеюующий год
const loading = ref(true)
const dates = ref<Record<string, boolean>>({})

onMounted(async () => {
  setTimeout(
    () =>
      document
        ?.querySelector('.vcalendar--today')
        ?.scrollIntoView({ block: 'center' }),
    100,
  )
})

async function loadData() {
  const { data } = await useHttp<ApiResponse<Vacations>>('vacations')
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
  useHttp('vacations', {
    method: 'post',
    body: { date },
  })
  // if (data.value) {
  //   vacations.value?.push(data.value as Vacation)
  // }
}

defineExpose({ open })
nextTick(loadData)
</script>

<template>
  <UiLoaderr :loading="loading" />
  <v-card class="vcalendar-card">
    <div class="vcalendar__header">
      <!-- <v-btn icon @click="dialog = false" variant="flat" :size="48">
          <v-icon icon="$close"></v-icon>
        </v-btn> -->
    </div>
    <div
      v-for="year in years"
      :key="year"
      class="vcalendar__year"
    >
      <h2>{{ year }}</h2>
      <div class="vcalendar">
        <div
          v-for="month in 12"
          :key="month"
          class="vcalendar__month"
        >
          <div class="vcalendar__month-label">
            <span class="text-grey-light">
              {{ monthLabels[month - 1] }}
            </span>
          </div>
          <div class="vcalendar__month-days">
            <div
              v-for="x in firstDayOfWeek(year, month)"
              :key="`x${x}`"
              class="no-pointer-events"
            />
            <div
              v-for="day in daysInMonth(year, month)"
              :key="day"
              :class="{
                'vcalendar--today': isToday(year, month, day),
                'vcalendar--selected': isSelected(year, month, day),
              }"
              @click="onClick(year, month, day)"
            >
              {{ day }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </v-card>
</template>

<style lang="scss">
.vcalendar {
  display: flex;
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  margin-top: 40px;
  color: black;
  gap: 30px 40px;
  &-card {
    align-items: flex-start;
    height: 100vh;
    overflow: scroll !important;
  }
  &--selected {
    background: rgb(var(--v-theme-primary)) !important;
    border-color: rgb(var(--v-theme-primary)) !important;
  }
  &--today {
    color: rgb(var(--v-theme-on-surface));
    border: 1px solid rgb(var(--v-theme-on-surface));
  }
  &__month {
    &-label {
      font-weight: bold;
      padding: 12px;
      color: rgb(var(--v-theme-grey));
    }
    &-days {
      display: grid;
      $size: 48px;
      grid-template-columns: repeat(7, $size);
      grid-gap: 3px;
      & > div {
        height: $size;
        // width: $size;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        // font-size: 12px;
        font-weight: 500;
        border-radius: 50%;
        // transition: background cubic-bezier(0.4, 0, 0.2, 1) 0.2s;
        // transition: background linear 0.1s;
        letter-spacing: 0.0892857143em;
        text-indent: 0.0892857143em;
        position: relative;
        &:hover {
          background: rgb(var(--v-theme-on-surface-variant));
        }
      }
    }
  }
  &__year {
    margin-bottom: 40px;
    margin-left: 20px;
    & > h2 {
      position: sticky;
      top: 18px;
      z-index: 99;
      margin-left: 10px;
      display: inline;
      color: black;
    }
  }
  &__header {
    position: sticky;
    top: 0;
    z-index: 1;
    height: 0;
    display: flex;
    justify-content: flex-end;
    width: 100%;
    & > .v-btn {
      margin-top: 11px;
      margin-right: 30px;
      background: transparent;
    }
    &:before {
      content: '';
      position: absolute;
      left: 0;
      top: 0;
      height: 70px;
      width: 106px;
      background: white;
      box-shadow: 0 0 10px 10px white;
    }
  }
}
</style>
