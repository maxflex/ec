<script setup lang="ts">
const { item } = defineProps<{
  item: ContractVersionListResource
}>()
const directionsCount = item.directions.length
</script>

<template>
  <span v-if="item.programs_count === 0" class="text-gray"> программ нет </span>
  <span v-else-if="directionsCount === 1">
    {{ RequestDirectionLabel[item.directions[0]] }} / {{ item.programs_count }}
  </span>
  <span v-else-if="directionsCount > 1">
    {{ plural(directionsCount, ['направление', 'направления', 'направлений']) }}
  </span>
  <span v-else>
    {{ plural(item.programs_count, ['программа', 'программы', 'программ']) }}
  </span>
</template>
