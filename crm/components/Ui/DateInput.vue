<script setup lang="ts">
const { modelValue } = withDefaults(
  defineProps<{
    label: string
    modelValue: string
  }>(),
  {
    label: 'Дата',
  },
)
const emit = defineEmits(['update:modelValue'])
const calendarDialog = ref()
</script>

<template>
  <div
    class="date-input"
    @click="() => calendarDialog.open()"
  >
    <v-text-field
      :label="label"
      :model-value="modelValue ? formatDate(modelValue) : null"
      hide-details
    />
  </div>
  <CalendarDialog
    ref="calendarDialog"
    :model-value="modelValue"
    @update:model-value="(v) => emit('update:modelValue', v)"
  />
</template>

<style lang="scss">
.date-input {
  cursor: pointer;
  .v-input {
    pointer-events: none;
  }
}
</style>
