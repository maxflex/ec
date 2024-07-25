<script setup lang="ts">
const { items } = defineProps<{
  items: InstructionTeacherResource[]
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
      <div style="width: 250px">
        <v-chip v-if="item.signed_at" class="text-success">
          подписано {{ formatDateTime(item.signed_at) }}
        </v-chip>

        <v-chip v-else class="text-error">
          не подписано
        </v-chip>
      </div>
      <div style="flex: initial; width: 150px" class="text-gray">
        {{ formatDateTime(item.created_at) }}
      </div>
    </NuxtLink>
  </div>
</template>
