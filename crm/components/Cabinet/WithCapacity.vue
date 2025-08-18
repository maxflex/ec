<script setup lang="ts">
import { Cabinets } from '.'

const { item } = defineProps<{ item: string }>()
const cabinet = Cabinets[item]
</script>

<template>
  <span>
    {{ cabinet.label }}<span v-if="cabinet.capacity">-{{ cabinet.capacity }}</span>
  </span>
</template>
