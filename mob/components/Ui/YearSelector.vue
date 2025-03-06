<script setup lang="ts">
const model = defineModel<Year>({ required: true })
</script>

<template>
  <v-select
    v-model="model"
    :items="selectItems(YearLabel)"
    label="Год"
  />
</template>
