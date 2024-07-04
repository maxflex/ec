<script setup lang="ts">
const props = withDefaults(
  defineProps<{
    label?: string
    year?: Year
    disabled?: boolean
  }>(),
  {
    label: 'Дата',
  },
)
const { label, year, disabled } = toRefs(props)
const model = defineModel<string>()
const calendarDialog = ref()
</script>

<template>
  <div
    class="date-input"
    :class="{
      'no-pointer-events': disabled,
    }"
    @click="() => calendarDialog.open()"
  >
    <v-text-field
      :label="label"
      :model-value="model ? formatDate(model) : null"
      hide-details
      :disabled="disabled"
    />
    <v-icon
      icon="$next"
    />
  </div>
  <CalendarDialog
    ref="calendarDialog"
    v-model="model"
    :year="year"
  />
</template>

<style lang="scss">
.date-input {
  cursor: pointer;
  position: relative;
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
}
</style>
