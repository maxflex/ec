<script setup lang="ts">
// const { multiple } = defineProps<{
//   multiple: boolean
// }>()

const model = defineModel<Program | Program[]>({ required: true })

// const items = selectItems(ProgramLabel)
//   .sort((a, b) => {
//     const extractNumber = (str: string) => {
//       const match = str.match(/^\d+/) // Extract leading number
//       return match ? Number.parseInt(match[0], 10) : Infinity // Use Infinity if no number
//     }
//
//     const numA = extractNumber(a.title)
//     const numB = extractNumber(b.title)
//
//     // Compare numeric prefixes first
//     if (numA !== numB) {
//       return numA - numB
//     }
//
//     // Fallback to alphabetical sorting
//     return a.title.localeCompare(b.title)
//   })
// .sort((a, b) => a.title.localeCompare(b.title))

// const extractNumber = (str: string) => {
//   const match = str.match(/^\d+/); // Extract leading number
//   return match ? parseInt(match[0], 10) : Infinity; // Use Infinity if no number
// };
//
// const numA = extractNumber(a.title);
// const numB = extractNumber(b.title);
//
// // Compare numeric prefixes first
// if (numA !== numB) {
//   return numA - numB;
// }
//
// // Fallback to alphabetical sorting
// return a.title.localeCompare(b.title);
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
