<script setup lang="ts">
const { item } = defineProps<{
  item: ClientDirections
}>()

const years = Object.keys(item)
  .map(y => Number.parseInt(y) as Year)

function formatYear(y: Year) {
  const year = y - 2000
  const nextYear = year + 1
  return `${year}-${nextYear}`
}
</script>

<template>
  <div class="client-directions">
    <div v-for="year in years" :key="year">
      {{ formatYear(year) }}: {{ item[year].map(d => DirectionLabel[d]).join(', ') }}
    </div>
  </div>
</template>
