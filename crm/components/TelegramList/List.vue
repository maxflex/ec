<script setup lang="ts">
const { items } = defineProps<{
  items: TelegramListResource[]
}>()
</script>

<template>
  <div class="table table--hover">
    <RouterLink
      v-for="item in items"
      :key="item.id"
      :to="{ name: 'telegram-lists-id', params: { id: item.id } }"
      class="table-item"
    >
      <div style="width: 250px">
        отправка от {{ formatDateTime(item.created_at!) }}
      </div>
      <div style="width: 200px">
        {{ SendToLabel[item.send_to] }}
      </div>
      <div style="width: 150px">
        получатели: {{ item.recipients.clients.length + item.recipients.teachers.length }}
      </div>
      <div style="width: 70px">
        <template v-if="item.results">
          <span class="text-success">
            {{ Object.values(item.results).filter(e => e.length).length }}
          </span>
          /
          <span class="text-error">
            {{ Object.values(item.results).filter(e => !e.length).length }}
          </span>
        </template>
      </div>
      <div style="width: 300px">
        <span v-if="item.scheduled_at">
          запланирована на {{ formatDateTime(item.scheduled_at) }}
        </span>
        <span v-else>
          мгновенная отправка
        </span>
      </div>
      <div>
        <TelegramListStatus :item="item" />
      </div>
    </RouterLink>
  </div>
</template>
