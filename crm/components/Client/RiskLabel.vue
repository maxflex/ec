<script setup lang="ts">
import { mdiAlertCircle } from '@mdi/js'

const { item } = defineProps<{
  item: {
    is_risk: boolean
  }
}>()
</script>

<template>
  <v-tooltip v-if="item.is_risk" location="bottom">
    <template #activator="{ props }">
      <v-icon :icon="mdiAlertCircle" v-bind="props" color="warning" :size="20" />
    </template>
    группа риска
  </v-tooltip>
</template>
