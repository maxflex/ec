<script setup lang="ts">
const { items, loading } = useIndex<InstructionListResource>(`instructions`)
</script>

<template>
  <UiFilters>
    <template #buttons>
      <v-btn
        append-icon="$next"
        color="primary"
        :ripple="false"
        :to="{
          name: 'instructions-create',
        }"
      >
        добавить инструкцию
      </v-btn>
    </template>
  </UiFilters>
  <div>
    <UiLoader3 :loading="loading" />
    <InstructionList :items="items" />
  </div>
</template>
