<script setup lang="ts">
import { YEARS } from "~/utils/sment"
const props = defineProps<{
  modelValue: string
}>()
const emit = defineEmits(["update:modelValue"])
const dialog = ref(false)
const monthLabels = [
  "январь",
  "февраль",
  "март",
  "апрель",
  "май",
  "июнь",
  "июль",
  "август",
  "сентябрь",
  "октябрь",
  "ноябрь",
  "декабрь",
]
const open = () => {
  dialog.value = true
  setTimeout(
    () =>
      document
        ?.querySelector(".calendar--selected")
        ?.scrollIntoView({ block: "center" }),
    100,
  )
}
const zeroPad = (value: number) => ("0" + value).slice(-2)

// отступ первого дня в календаре
const firstDayOfWeek = (year: number, month: number) =>
  new Date(year, month - 1, 0).getDay()

const daysInMonth = (year: number, month: number) =>
  new Date(year, month, 0).getDate()

const getDate = (year: number, month: number, day: number): string =>
  [year, zeroPad(month), zeroPad(day)].join("-")

const isToday = (year: number, month: number, day: number) =>
  getDate(year, month, day) === today()

const isSelected = (year: number, month: number, day: number) =>
  getDate(year, month, day) === props.modelValue

const onClick = (year: number, month: number, day: number) => {
  emit("update:modelValue", getDate(year, month, day))
  dialog.value = false
}

defineExpose({ open })
</script>

<template>
  <v-dialog
    :scrim="false"
    v-model="dialog"
    transition="dialog-bottom-transition"
    fullscreen
  >
    <v-card class="calendar-card">
      <div class="calendar__header">
        <v-btn icon @click="dialog = false" variant="flat" :size="48">
          <v-icon icon="$close"></v-icon>
        </v-btn>
      </div>
      <div class="calendar__year" v-for="year in YEARS.toReversed()">
        <h2>{{ year }}</h2>
        <div class="calendar">
          <div v-for="month in 12" :key="month" class="calendar__month">
            <div class="calendar__month-label">
              <span class="text-grey-light">
                {{ monthLabels[month - 1] }}
              </span>
            </div>
            <div class="calendar__month-days">
              <div
                v-for="x in firstDayOfWeek(year, month)"
                :key="'x' + x"
                class="no-pointer-events"
              ></div>
              <div
                v-for="day in daysInMonth(year, month)"
                :key="day"
                :class="{
                  'calendar--today': isToday(year, month, day),
                  'calendar--selected': isSelected(year, month, day),
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
  </v-dialog>
</template>

<style lang="scss">
.calendar {
  display: flex;
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  margin-top: 40px;
  color: black;
  gap: 30px 60px;
  &-card {
    align-items: flex-start;
  }
  &--selected {
    background: rgb(var(--v-theme-secondary));
    border-color: rgb(var(--v-theme-secondary)) !important;
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
    margin-left: 30px;
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
      content: "";
      position: absolute;
      left: 0;
      top: 0;
      height: 70px;
      width: 100%;
      // background: #f2f4f8;
      background: white;
      box-shadow: 0 0 10px 10px white;
    }
  }
}
</style>
