<script setup lang="ts">
import type { CallAonResource } from '../Call'

const { item } = defineProps<{
  item: CallAonResource | null
}>()
</script>

<template>
  <div v-if="item === null" class="text-gray">
    Неизвестный
  </div>
  <div v-else-if="item.entity">
    <UiPerson :item="item.entity" no-name>
      {{ EntityTypeLabel[item.entity.entity_type] }}
      {{ item.entity.id }}
    </UiPerson>
  </div>
  <div v-else-if="item.request_id">
    <RouterLink :to="{ name: 'requests-id', params: { id: item.request_id } }" @click.stop>
      Заявка
      {{ item.request_id }}
    </RouterLink>
  </div>
</template>
