<script setup lang="ts">
import type { ClientDirection } from '.'
import { sortBy } from 'lodash-es'

const { items } = defineProps<{
  items: ClientDirection[]
}>()

const itemsByYear = sortBy(items, 'year')

function formatYear(y: Year) {
  const year = y - 2000
  const nextYear = year + 1
  return `${year}-${nextYear}`
}
</script>

<template>
  <div
    v-if="items.length"
    class="client-directions"
  >
    <div
      v-for="item in itemsByYear" :key="item.id" class="text-truncate"
      :class="`client-directions--${item.status}`"
    >
      {{ formatYear(item.year) }}: {{ DirectionLabel[item.direction] }}
    </div>
  </div>
  <span v-else class="text-gray">
    не установлено
  </span>
</template>

<style lang="scss">
.client-directions {
  &--finished {
    color: rgb(var(--v-theme-gray)) !important;
  }
  &--exceeded {
    color: rgb(var(--v-theme-error)) !important;
  }
}
</style>
