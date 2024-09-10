<script setup lang="ts">
const { items } = defineProps<{
  items: ErrorResource[]
}>()
</script>

<template>
  <div class="table">
    <div v-for="item in items" :key="item.id">
      <div style="flex: 1">
        <RouterLink :to="`teachers/${item.entity_id}`">
          {{ formatFullName(item.person!) }}
        </RouterLink>
      </div>
      <div style="width: 300px">
        <template v-if="item.number">
          {{ item.number }}
        </template>
      </div>
      <div style="width: 100px; flex: initial">
        <code>
          {{ item.code }}
        </code>
      </div>
    </div>
  </div>
</template>
