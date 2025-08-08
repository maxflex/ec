<script setup lang="ts">
import { Cabinets } from '.'

const { items } = defineProps<{ items: string[] }>()
</script>

<template>
  <div v-for="c in items" :key="c">
    {{ Cabinets[c].label }}<span v-if="Cabinets[c].capacity">-{{ Cabinets[c].capacity }}</span>
  </div>
</template>
