<script setup lang="ts">
import type { MenuItem } from '.'
import { menuCounts } from '.'

const { item } = defineProps<{
  item: MenuItem
}>()

const count = computed<number | boolean>(() => {
  if (!item.count) {
    return 0
  }
  const key = item.to.slice(1)
  return menuCounts.value[key] || 0
})
</script>

<template>
  <v-list-item :to="item.to">
    <template v-if="item.icon" #prepend>
      <v-icon :icon="item.icon" />
    </template>
    {{ item.title }}
    <template v-if="count" #append>
      <UiCircle v-if="typeof (count) === 'boolean'" color="error" class="pr-1" />
      <v-badge
        v-else
        color="error"
        inline
        :content="count as number"
      />
    </template>
  </v-list-item>
</template>
