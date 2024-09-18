<script lang="ts" setup>
const model = defineModel<ClientPaymentFilters>({ required: true })
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
      v-model="model.method"
      label="Метод"
      :items="selectItems(ClientPaymentMethodLabel)"
      density="comfortable"
    />
  </div>
</template>
