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
    <template #append>
      <v-fade-transition mode="out-in">
        <UiCircle
          v-if="count && typeof (count) === 'boolean'"
          key="circle"
          color="error"
          class="pr-1"
        />
        <MenuCount
          v-else-if="count"
          key="count"
          :count="count as number"
        />
      </v-fade-transition>
    </template>
  </v-list-item>
</template>
