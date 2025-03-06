<script lang="ts" setup>
const model = defineModel({ required: true })
</script>

<template>
  <v-select
    v-model="model.year"
    :items="selectItems(YearLabel)"
    label="Учебный год"
    density="comfortable"
  />
  <!--      <UiClearableSelect -->
  <!--        v-model="model.program" -->
  <!--        :items="selectItems(ProgramLabel)" label="Программа" -->
  <!--        density="comfortable" -->
  <!--      /> -->
  <UiClearableSelect
    v-model="model.status"
    :items="selectItems(ClientTestStatusLabel)"
    label="Статус"
    density="comfortable"
  />
</template>
