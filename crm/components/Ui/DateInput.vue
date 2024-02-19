<script setup lang="ts">
const { modelValue } = defineProps<{
  modelValue: string
}>()
const emit = defineEmits(["update:modelValue"])
const calendarDialog = ref()
</script>

<template>
  <div class="date-input" @click="() => calendarDialog.open()">
    <v-text-field label="Дата" :model-value="formatDate(modelValue)" />
  </div>
  <CalendarDialog
    @update:model-value="(v) => emit('update:modelValue', v)"
    :model-value="modelValue"
    ref="calendarDialog"
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
