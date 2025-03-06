<script setup lang="ts">
const { items } = defineProps<{
  items: ClientWithContractsResource[]
}>()
const model = defineModel<number | null>()
</script>

<template>
  <UiClearableSelect
    v-model="model"
    v-bind="$attrs"
    :items="items"
    :item-title="formatName"
    item-value="id"
    label="Клиент"
  >
    <template #item="{ props, item }">
      <v-list-item v-bind="props">
        <template #prepend />
        <template #title>
          {{ item.title }}
        </template>
        <template #subtitle>
          <div v-if="item.raw.contract_versions">
            <div v-if="item.raw.contract_versions.length === 0">
              нет договоров
            </div>
            <div v-for="cv in item.raw.contract_versions" :key="cv.id">
              договор №{{ cv.contract.id }} на {{ YearLabel[cv.contract.year as Year] }}
            </div>
          </div>
        </template>
      </v-list-item>
    </template>
  </UiClearableSelect>
</template>
