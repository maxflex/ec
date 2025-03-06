<script lang="ts" setup>
const model = defineModel<ClientFilters>({ required: true })
</script>

<template>
  <UiChipSelector
    v-model="model.year"
    :label="YearLabel[currentAcademicYear()]"
    :items="selectItems(YearLabel)"
    density="comfortable"
  />
</template>
