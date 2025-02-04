<script setup lang="ts">
const { item, full } = defineProps<{
  item: CallAppAonResource | null
  full?: boolean
}>()
</script>

<template>
  <div v-if="item === null">
    Неизвестный
  </div>
  <template v-else>
    <div v-if="full && item.comment">
      {{ item.comment }}
    </div>
    <div v-if="item.entity">
      <UiPerson :item="item.entity" :no-link="!full" />
      ({{ EntityTypeLabel[item.entity.entity_type] }})
    </div>
    <div v-else-if="item.request_id">
      <RouterLink :to="{ name: 'requests-id', params: { id: item.request_id } }" @click.stop>
        {{ item.request_id }}
      </RouterLink>
      (Заявка)
    </div>
  </template>
</template>
