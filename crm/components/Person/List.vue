<script setup lang="ts">
const { items } = defineProps<{
  items: PersonResource[]
}>()
</script>

<template>
  <div class="table">
    <div v-for="p in items" :key="p.id">
      <div>
        {{ formatName(p) }}
      </div>
    </div>
  </div>
</template>
