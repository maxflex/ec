<script setup lang="ts">
import type { SwampListResource } from '.'

const { item } = defineProps<{
  item: SwampListResource
}>()
</script>

<template>
  <div v-if="item.changes">
    <template v-if="item.changes.type === 'added'">
      добавлен в
    </template>
    <template v-else-if="item.changes.type === 'changed'">
      уходит в
    </template>
    <template v-else>
      уходит из группы
    </template>
    <RouterLink
      v-if="item.changes.group_id"
      :to="{ name: 'groups-id', params: { id: item.changes.group_id } }"
    >
      ГР-{{ item.changes.group_id }}
    </RouterLink>
    <br />
    в проекте

    <RouterLink
      :to="{
        name: 'projects-editor',
        query: {
          id: item.changes.project_id,
        },
      }"
    >
      {{ item.changes.project_id }}
    </RouterLink>
  </div>
</template>
