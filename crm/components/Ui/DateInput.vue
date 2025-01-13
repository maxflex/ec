<script setup lang="ts">
const { label = 'Дата', year, disabled, past } = defineProps<{
  label?: string
  year?: Year
  disabled?: boolean
  todayBtn?: boolean
  past?: boolean
  clearable?: boolean
  density?: string
  placeholder?: string | null
}>()

const model = defineModel<string | null>({ required: true })
const calendarDialog = ref()

function setToday() {
  model.value = today()
}

function clear() {
  model.value = null
}
</script>

<template>
  <div
    class="date-input"
    :class="{
      'no-pointer-events': disabled,
    }"
  >
    <div
      :class="{
        [`date-input__input date-input__input--${density}`]: true,
        'date-input--no-value': !model,
      }"
      @click="() => calendarDialog.open()"
    >
      <v-text-field
        :label="label"
        :model-value="model ? formatDate(model) : (placeholder || null)"
        hide-details
        :disabled="disabled"
        :density="density"
      />
      <v-icon
        icon="$next"
      />
    </div>
    <a v-if="todayBtn" class="date-input__today" @click="setToday()">
      сегодня
    </a>
    <a v-if="clearable && model" class="date-input__today" @click="clear()">
      очистить
    </a>
  </div>

  <CalendarDialog
    ref="calendarDialog"
    v-model="model"
    :year="year"
    :past="past"
  />
</template>

<style lang="scss">
.date-input {
  position: relative;
  &__input {
    cursor: pointer;
    .v-input {
      pointer-events: none;
    }
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
}
</style>
