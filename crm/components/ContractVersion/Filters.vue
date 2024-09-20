<script lang="ts" setup>
const model = defineModel<ContractVersionFilters>({ required: true })
</script>

<template>
  <div>
    <v-select
      v-model="model.year"
      label="Учебный год"
      :items="selectItems(YearLabel)"
      density="comfortable"
    />
  </div>
  <div>
    <UiClearableSelect
      v-model="model.company"
      :items="selectItems(CompanyLabel)"
      density="comfortable"
      label="Тип"
    />
  </div>
  <div>
    <UiClearableSelect
      v-model="model.version"
      :items="yesNo('первая', 'последняя')"
      density="comfortable"
      label="Версия"
    />
  </div>
</template>
