<script setup lang="ts">
const model = defineModel<Year>({ required: true })
</script>

<template>
  <v-select
    v-bind="$attrs"
    v-model="model"
    :items="selectItems(YearLabel)"
    label="Учебный год"
  />
</template>
