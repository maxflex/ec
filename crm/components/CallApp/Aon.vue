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
      {{ EntityTypeLabel[item.entity.entity_type] }}:
      <UiPerson :item="item.entity" :no-link="full && !!item.client_id" />
    </div>
    <div v-else-if="item.request_id">
      Заявка:
      <RouterLink :to="{ name: 'requests-id', params: { id: item.request_id } }" @click.stop>
        {{ item.request_id }}
      </RouterLink>
    </div>
    <div v-if="full && item.client_id">
      Клиент:
      <RouterLink :to="{ name: 'clients-id', params: { id: item.client_id } }" @click.stop>
        {{ item.client_id }}
      </RouterLink>
    </div>
  </template>
</template>
