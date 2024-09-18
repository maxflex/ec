<script setup lang="ts">
const { items, indexPageData } = useIndex<InstructionListResource>(`instructions`)
</script>

<template>
  <UiIndexPage :data="indexPageData">
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
    <InstructionList :items="items" />
  </UiIndexPage>
</template>
