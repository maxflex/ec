<script lang="ts" setup>
export interface ContractFilters {
  year: Year
  direction?: Direction
}

const model = defineModel<ContractFilters>({ required: true })
</script>

<template>
  <UiChipSelector
    v-model="model.year"
    :label="YearLabel[currentAcademicYear()]"
    :items="selectItems(YearLabel)"
    density="comfortable"
  />
  <UiChipSelector
    v-model="model.direction"
    clearable
    :items="selectItems(DirectionLabel)"
    label="направление"
    density="comfortable"
  />
</template>
