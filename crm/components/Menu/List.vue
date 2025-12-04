<script setup lang="ts">
import type { Menu, MenuItem, Submenu } from '.'

const { items } = defineProps<{
  items: Menu
}>()

function isSubmenu(m: MenuItem | Submenu): m is Submenu {
  return 'items' in m
}
</script>

<template>
  <template v-for="m in items">
    <v-list-group v-if="isSubmenu(m)" :key="m.title" :value="m.title">
      <template #activator="{ props }">
        <v-list-item v-bind="props">
          <template #prepend>
            <v-icon :icon="m.icon" />
          </template>
          {{ m.title }}
        </v-list-item>
      </template>
      <MenuItem v-for="menuItem in m.items" :key="menuItem.to" :item="menuItem" />
    </v-list-group>
    <MenuItem v-else :key="m.to" :item="m as MenuItem" />
  </template>
</template>
