<script lang="ts" setup>
export interface ClientPaymentFilters {
  year: Year
  method?: ClientPaymentMethod
}

const model = defineModel<ClientPaymentFilters>({ required: true })
</script>

<template>
  <v-select
    v-model="model.year"
    label="Учебный год"
    :items="selectItems(YearLabel)"
    density="comfortable"
  />
  <UiClearableSelect
    v-model="model.method"
    label="Метод"
    :items="selectItems(ClientPaymentMethodLabel)"
    density="comfortable"
  />
</template>
