<script setup lang="ts">
const { item, hideOld } = defineProps<{
  item: ClientDirections
  hideOld?: boolean
}>()

const currentYear = currentAcademicYear()
const years = Object.keys(item)
  .map(y => Number.parseInt(y) as Year)
  .filter(y => (hideOld ? y >= currentYear : true))

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
