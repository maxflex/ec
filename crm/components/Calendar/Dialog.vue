<script setup lang="ts">
const { year } = defineProps<{
  year?: Year
}>()
const { dialog, width, transition } = useDialog('medium')
const model = defineModel<string>()
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
                {{ monthLabels[m - 1] }}
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

<style lang="scss">
.calendar {
  display: flex;
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  margin-top: 40px;
  color: black;
  gap: 30px 50px;
  &-card {
    align-items: flex-start;
  }
  &--selected {
    background: rgb(var(--v-theme-primary));
    border-color: rgb(var(--v-theme-primary)) !important;
    // color: white !important;
    pointer-events: none;
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
