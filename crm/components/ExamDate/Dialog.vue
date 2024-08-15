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
const year = currentAcademicYear()
const months = [9, 10, 11, 12, 1, 2, 3, 4, 5, 6]
const saving = ref(false)

function edit(ed: ExamDateResource) {
  item.value = clone(ed)
  dialog.value = true
  setTimeout(() => {
    const selectedElement = document.querySelector('.calendar--selected-clickable')
    if (selectedElement) {
      selectedElement.scrollIntoView({ block: 'center' })
    }
    else {
      smoothScroll('dialog', 'bottom', 'instant')
    }
  }, 100)
}

// отступ первого дня в календаре
function firstDayOfWeek(m: number) {
  return new Date(year, m - 1, 0).getDay()
}

function daysInMonth(m: number) {
  return new Date(year, m, 0).getDate()
}

function zeroPad(value: number): string {
  return (`0${value}`).slice(-2)
}

function getDate(m: number, d: number): string {
  return [year, zeroPad(m), zeroPad(d)].join('-')
}

function isSelected(m: number, d: number) {
  const date = getDate(m, d)
  return item.value.dates.includes(date)
}

function onDateClick(m: number, d: number) {
  const date = getDate(m, d)
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
          <span v-if="item.dates.length" class="ml-1 text-gray">
            {{ item.dates.length }}
          </span>
        </div>
        <v-btn
          :size="48"
          icon="$save"
          variant="text"
          :loading="saving"
          @click="save()"
        />
      </div>
      <div class="dialog-body pt-4">
        <div class="calendar mt-0">
          <div v-for="m in months" :key="m" class="calendar__month">
            <div class="calendar__month-label">
              <span class="text-grey-light">
                {{ MonthLabel[m - 1] }}
              </span>
            </div>
            <div class="calendar__month-days">
              <div
                v-for="x in firstDayOfWeek(m)"
                :key="`x${x}`"
                class="no-pointer-events"
              />
              <div
                v-for="d in daysInMonth(m)"
                :key="d"
                :class="{
                  'calendar--selected-clickable': isSelected(m, d),
                }"
                @click="onDateClick(m, d)"
              >
                {{ d }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </v-dialog>
</template>
