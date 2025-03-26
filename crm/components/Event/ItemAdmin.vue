<script setup lang="ts">
const { item } = defineProps<{ item: EventListResource }>()

onMounted(() => console.log('EVENT', item))
</script>

<template>
  <div
    :id="`event-${item.id}`"
    class="lesson-item event-item"
  >
    <div style="width: 110px">
      {{ formatTime(item.time!) }}
      <template v-if="item.time_end">
        – {{ item.time_end }}
      </template>
    </div>
    <div style="width: 50px">
    </div>
    <div style="width: 140px">
      {{ formatName(item.user) }}
    </div>
    <div style="width: 200px">
      <RouterLink :to="{ name: 'events-id', params: { id: item.id } }">
        {{ item.name }}
      </RouterLink>
    </div>
    <div style="width: 250px">
      {{ item.participants_count }} участников
    </div>
    <div class="event-item__status">
      <div>
        {{ item.is_afterclass ? 'внеклассное' : 'учебное' }} событие
      </div>
      <div v-if="item.is_private" class="text-purple">
        конфиденциальное
      </div>
    </div>
  </div>
</template>
