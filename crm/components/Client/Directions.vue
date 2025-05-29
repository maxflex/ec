<script setup lang="ts">
const { item, hideOld } = defineProps<{
  item: ClientDirections
  hideOld?: boolean
}>()

const currentYear = currentAcademicYear()
const years = Object.keys(item)
  .map((y) => Number.parseInt(y) as Year)
  .filter((y) => (hideOld ? y >= currentYear : true))

function formatYear(y: Year) {
  const year = y - 2000
  const nextYear = year + 1
  return `${year}-${nextYear}`
}
</script>

<template>
  <div class="client-directions">
    <template v-for="year in years" :key="year">
      <div v-for="d in item[year]" :key="d" class="nowrap">
        {{ DirectionLabel[d] }} / {{ formatYear(year) }}
      </div>
    </template>
  </div>
</template>
