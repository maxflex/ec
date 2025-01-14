<script setup lang="ts">
// const { multiple } = defineProps<{
//   multiple: boolean
// }>()

const model = defineModel<Program | Program[]>({ required: true })
</script>

<template>
  <v-autocomplete
    v-model="model"
    :items="selectItems(ProgramLabel)"
    label="Программа"
    variant="outlined"
    density="comfortable"
    hide-details
  >
    <template v-if="Array.isArray(model) && model.length > 1" #selection="{ index }">
      <template v-if="index === 0">
        выбрано: {{ model.length }}
      </template>
    </template>
    <template #no-data>
      <v-list-item>
        ничего не найдено
      </v-list-item>
    </template>
  </v-autocomplete>
</template>
