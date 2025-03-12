<script setup lang="ts">
const emit = defineEmits(['close'])
const model = defineModel<string | string[]>({ required: true })
const dialog = ref(false)

// начиная с какой даты возможен выбор в календаре
const startYear = 2015
const width = 840
const years = Array.from({ length: currentAcademicYear() - startYear + 4 }, (_, i) => startYear + i)
const scrolling = ref(true)

let el: {
  overlay: HTMLElement
  dialog: HTMLElement
  clonedScrim: HTMLElement
} | null = null

// const years = year === undefined
//   ? Array.from({ length: currentAcademicYear() - startYear + 2 }, (_, i) => startYear + i)
//   : [year - 1, year, year + 1]

function open() {
  dialog.value = true
  handleLowerDialogOnOpen()
  scrollToActiveDate()
}

function setElements() {
  if (el !== null) {
    return
  }
  const overlay = document.documentElement.querySelector<HTMLElement>('.v-dialog.v-overlay--active')!

  el = {
    overlay,
    dialog: overlay.querySelector<HTMLElement>(':scope > .dialog')!,
    clonedScrim: overlay.querySelector<HTMLElement>(':scope > .v-overlay__scrim')!.cloneNode() as HTMLElement,
  }
}

function handleLowerDialogOnOpen() {
  setElements()

  if (!el) {
    return
  }

  el.dialog.style.overflow = 'visible'
  el.dialog.style.right = `${width}px`
  el.dialog.classList.add('calendar-open')

  el.clonedScrim.style.opacity = '0'
  el.clonedScrim.addEventListener('click', close)
  el.overlay.insertBefore(
    el.clonedScrim,
    el.dialog,
  )
}

function handleLowerDialogOnClose() {
  if (!el) {
    return
  }

  el.dialog.style.right = '0'
  el.dialog.classList.remove('calendar-open')
  el.overlay.removeChild(el.clonedScrim)
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
    close()
  }
}

function close() {
  handleLowerDialogOnClose()
  emit('close')
  setTimeout(() => dialog.value = false, 500)
}

defineExpose({ open })
</script>

<template>
  <div v-if="dialog" class="calendar-dialog" :style="{ width: `${width}px` }">
    <v-fade-transition>
      <UiLoader v-if="scrolling" style="z-index: 1" />
    </v-fade-transition>
    <div class="calendar-dialog__header">
      <v-btn icon variant="flat" :size="48" color="primary" @click="close()">
        <v-icon icon="$close" color="black"></v-icon>
      </v-btn>
    </div>
    <div v-for="y in years" :key="y" class="calendar__year">
      <h2>{{ y }}</h2>
      <div class="calendar">
        <div v-for="m in 12" :key="m" class="calendar__month">
          <div class="calendar__month-label">
            <span class="text-grey-light">
              {{ MonthLabel[m as Month] }}
            </span>
          </div>
          <div class="calendar__month-days">
            <div v-for="x in firstDayOfWeek(y, m)" :key="`x${x}`" class="no-pointer-events" />
            <div
              v-for="d in daysInMonth(y, m)" :key="d" :class="{
                'calendar--today': isToday(y, m, d),
                'calendar--selected': isSelected(y, m, d),
              }" @click="onClick(y, m, d)"
            >
              {{ d }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- <div v-if="dialog" class="calendar-dialog__scrim" @click="close()">
  </div> -->
</template>
