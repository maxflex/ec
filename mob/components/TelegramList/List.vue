<script setup lang="ts">
const { items } = defineProps<{
  items: TelegramListResource[]
}>()

function getRecipientsCount(l: TelegramListResource) {
  const { clients, teachers } = l.recipients
  return (clients.length * (l.send_to === 'studentsAndParents' ? 2 : 1)) + teachers.length
}

function getSentCount(l: TelegramListResource): number {
  if (!l.results) {
    return 0
  }
  return Object.entries(l.results).reduce((carry, [key, val]) => {
    if (key.startsWith('teachers')) {
      carry += val.some(y => y.is_sent) ? 1 : 0
    }
    else {
      if (l.send_to !== 'students') {
        carry += val.some(y => y.is_sent && y.is_parent) ? 1 : 0
      }
      if (l.send_to !== 'parents') {
        carry += val.some(y => y.is_sent && !y.is_parent) ? 1 : 0
      }
    }
    return carry
  }, 0)
}
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
        получатели: {{ getRecipientsCount(item) }}
      </div>

      <div style="width: 320px">
        <span v-if="item.scheduled_at">
          запланирована на {{ formatDateTime(item.scheduled_at) }}
        </span>
        <span v-else>
          мгновенная отправка
        </span>
      </div>
      <div style="width: 140px">
        <TelegramListStatus :item="item" />
      </div>
      <div :class="{ 'opacity-5': item.status !== 'sent' }">
        <template v-if="item.results">
          <span class="text-success">
            {{ getSentCount(item) }}
          </span>
          /
          <span class="text-error">
            {{ getRecipientsCount(item) - getSentCount(item) }}
          </span>
        </template>
      </div>
    </RouterLink>
  </div>
</template>
