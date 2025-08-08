<script setup lang="ts">
import { Cabinets } from '.'

const { cabinet } = defineProps<{ cabinet: string }>()
</script>

<template>
  <span>
    {{ Cabinets[cabinet].label }}<span v-if="Cabinets[cabinet].capacity">-{{ Cabinets[cabinet].capacity }}</span>
  </span>
</template>
