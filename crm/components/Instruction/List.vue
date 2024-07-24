<script setup lang="ts">
const { items } = defineProps<{
  items: InstructionListResource[]
}>()
</script>

<template>
  <div class="table table--hover">
    <NuxtLink
      v-for="item in items"
      :id="`instruction-${item.id}`"
      :key="item.id"
      class="table-item"
      :to="{ name: 'instructions-id', params: { id: item.id } }"
    >
      <div style="flex: 1">
        {{ item.title }}
      </div>
      <div style="width: 120px">
        {{ plural(item.versions_count, ['версия', 'версии', 'версий']) }}
      </div>
      <div style="width: 140px">
        {{ item.signs_count }} подписали
      </div>
      <div style="flex: initial; width: 150px" class="text-gray">
        {{ formatDateTime(item.created_at) }}
      </div>
    </NuxtLink>
  </div>
</template>
