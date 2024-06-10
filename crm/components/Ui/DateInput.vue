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
  </div>
  <CalendarDialog
    ref="calendarDialog"
    v-model="model"
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
