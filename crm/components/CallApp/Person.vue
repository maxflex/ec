<script setup lang="ts">
const { item } = defineProps<{
  item: CallAppAonResource | null
}>()
</script>

<template>
  <div v-if="item === null">
    Неизвестный
  </div>
  <div v-else-if="item.entity">
    <UiPerson :item="item.entity">
      ({{ EntityTypeLabel[item.entity.entity_type] }})
    </UiPerson>
  </div>
  <div v-else-if="item.request_id">
    <RouterLink :to="{ name: 'requests-id', params: { id: item.request_id } }" @click.stop>
      {{ item.request_id }}
      (Заявка)
    </RouterLink>
  </div>
</template>
