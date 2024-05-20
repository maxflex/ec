<script setup lang="ts">
import { YEARS } from '~/utils/sment'

const model = defineModel<number>()
</script>

<template>
  <v-select
    v-model="model"
    label="Учебный год"
    :items="
      YEARS.map((value) => ({
        value,
        title: formatYear(value),
      }))
    "
  />
</template>
