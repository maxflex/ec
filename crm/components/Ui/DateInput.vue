<script setup lang="ts">
const { label = 'Дата', year, disabled, manual } = defineProps<{
  label?: string
  year?: Year
  disabled?: boolean
  todayBtn?: boolean
  manual?: boolean
  clearable?: boolean
  density?: string
  error?: boolean
  placeholder?: string | null
}>()

const model = defineModel<string | null>({ required: true })
const dateManual = ref(model.value === null ? '' : formatDate(model.value))
const calendarDialog = ref()
const dateMask = { mask: '##.##.####' }

function setToday() {
  model.value = today()
}

function clear() {
  model.value = null
}

function onDateSelected(date: string) {
  model.value = date
  if (manual) {
    dateManual.value = formatDate(date)
  }
}

const stopWatch = watch(model, (newVal, oldVal) => {
  if (oldVal === null && newVal) {
    dateManual.value = formatDate(newVal)
    stopWatch()
  }
})

function onManualKeyup() {
  if (dateManual.value.length === 10) {
    const [d, m, y] = dateManual.value.split('.')
    model.value = `${y}-${m}-${d}`
  }
  else {
    model.value = null
  }
}
</script>

<template>
  <div
    class="date-input"
    :class="{
      'no-pointer-events': disabled,
      'date-input--manual': manual,
    }"
  >
    <div
      :class="{
        [`date-input__input date-input__input--${density}`]: true,
        'date-input--no-value': !model,
      }"
      @click="() => !manual && calendarDialog.open()"
    >
      <v-text-field
        v-if="manual"
        v-model="dateManual"
        v-maska="dateMask"
        :label="label"
        :disabled="disabled"
        :density="density"
        :error="error"
        @keyup="onManualKeyup()"
      />
      <v-text-field
        v-else
        :label="label"
        :model-value="model ? formatDate(model) : (placeholder || null)"
        hide-details
        :disabled="disabled"
        :density="density"
      />
      <v-icon icon="$next" @click="calendarDialog.open() " />
    </div>
    <template v-if="!disabled">
      <a v-if="todayBtn" class="date-input__today" @click="setToday()">
        сегодня
      </a>
      <a v-if="clearable && model" class="date-input__today" @click="clear()">
        очистить
      </a>
    </template>
  </div>

  <CalendarDialog
    ref="calendarDialog"
    :model-value="model"
    :year="year"
    @update:model-value="onDateSelected"
  />
</template>

<style lang="scss">
.date-input {
  position: relative;
  &__input {
    cursor: pointer;
    & > .v-icon {
      opacity: 0;
      // opacity: 0.6;
      position: absolute;
      top: 16px;
      right: 12px;
      transition: all 0.2s cubic-bezier(0.2, 0, 0.4, 0.9);
    }
    &:hover {
      .v-field__outline {
        --v-field-border-opacity: var(--v-high-emphasis-opacity);
      }
      & > .v-icon {
        opacity: 0.6;
        right: 6px;
      }
    }
    &--compact {
      & > .v-icon {
        top: 13px !important;
      }
    }
    &:has(.v-input--density-comfortable) {
      & > .v-icon {
        top: 13px !important;
      }
    }
  }
  &__today {
    margin-left: 16px;
    font-size: 14px;
    cursor: pointer;
  }
  &--no-value {
    input {
      color: rgb(var(--v-theme-label));
    }
  }
  &:not(.date-input--manual) .date-input__input {
    .v-input {
      pointer-events: none;
    }
  }
}
</style>
