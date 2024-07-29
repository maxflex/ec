<script setup lang="ts">
const { items } = defineProps<{ items: any }>()
</script>

<template>
  <div class="table">
    <div v-for="swamp in items" :key="swamp.id">
      <div>
        {{ swamp }}
      </div>
    </div>
  </div>
</template>
