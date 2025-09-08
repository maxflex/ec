<template>
  <div v-if="item.changes">
    <template v-if="item.changes.type === 'added'">
      добавлен в
    </template>
    <template v-else-if="item.changes.type === 'changed'">
      перемещён
    </template>
    <template v-else>
      удалён
    </template>
    <RouterLink v-if="item.changes.group" :to="{ name: 'groups-id', params: { id: item.changes.group.id } }">
      ГР-{{ item.changes.group.id }}
    </RouterLink>
    <br />
    в проекте

    <RouterLink
      :to="{
        name: 'schedule-drafts-editor',
        query: {
          id: item.changes.schedule_draft_id,
        },
      }"
    >
      {{ item.changes.schedule_draft_id }}
    </RouterLink>
  </div>
</template>
