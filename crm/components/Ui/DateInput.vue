<script setup lang="ts">
const { label } = withDefaults(
  defineProps<{
    label?: string
  }>(),
  {
    label: 'Дата',
  },
)
const model = defineModel<string>()
const calendarDialog = ref()
</script>

<template>
  <div
    class="date-input"
    @click="() => calendarDialog.open()"
  >
    <v-text-field
      :label="label"
      :model-value="model ? formatDate(model) : null"
      hide-details
    />
    <v-icon
      icon="$next"
    />
  </div>
  <CalendarDialog
    ref="calendarDialog"
    v-model="model"
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
