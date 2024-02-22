<script setup lang="ts">
import { YEARS } from "~/utils/sment"
const { modelValue } = defineProps<{
  modelValue: number
}>()
</script>

<template>
  <v-select
    label="Учебный год"
    :items="
      YEARS.map((value) => ({
        value,
        title: formatYear(value),
      }))
    "
    :model-value="modelValue"
    @update:model-value="(e) => $emit('update:model-value', e)"
  />
</template>
