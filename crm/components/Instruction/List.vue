<script setup lang="ts">
const { items } = defineProps<{
  items: InstructionListResource[]
}>()
</script>

<template>
  <div class="table table--hover">
    <NuxtLink
      v-for="item in items"
      :key="item.id"
      class="table-item"
      :to="{ name: 'instructions-id', params: { id: item.id } }"
    >
      <div style="flex: 1">
        {{ item.title }}
      </div>
      <div style="flex: inital; width: 200px">
        {{ plural(item.versions_count, ['версия', 'версии', 'версий']) }}
      </div>
    </NuxtLink>
  </div>
</template>
