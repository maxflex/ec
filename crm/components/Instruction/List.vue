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
      :to="{ path: `${$route.path}/${item.id}` }"
    >
      <div style="flex: 1">
        {{ item.title }}
      </div>
      <div style="width: 220px">
        {{ InstructionStatusLabel[item.status] }}
      </div>
      <div style="width: 120px">
        {{ plural(item.versions_count, ['версия', 'версии', 'версий']) }}
      </div>
      <div style="width: 160px">
        {{ item.signs_needed - item.signs_count }} не подписали
      </div>
      <div style="flex: initial; width: 150px" class="text-gray">
        {{ formatDateTime(item.created_at) }}
      </div>
    </NuxtLink>
  </div>
</template>
