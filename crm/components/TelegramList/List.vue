<script setup lang="ts">
const { items } = defineProps<{
  items: TelegramListResource[]
}>()

function getTotal(tgList: TelegramListResource, onlySent: boolean = false): number {
  let result = 0

  for (const sentTo in tgList.result) {
    for (const { messages } of tgList.result[sentTo as SendTo]) {
      result += onlySent ? messages.filter(m => !!m.telegram_id).length : messages.length
    }
  }

  return result
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
      <div style="width: 300px">
        <span v-if="item.status === 'sent'">
          отправлено {{ formatDateTime(item.scheduled_at || item.created_at!) }}
        </span>
        <span v-else-if="item.scheduled_at" class="text-gray">
          запланирована на {{ formatDateTime(item.scheduled_at) }}
        </span>
        <span v-else class="text-gray">
          мгновенная отправка
        </span>
        <!-- <TelegramListStatus :item="item" /> -->
      </div>

      <div style="width: 260px">
        <TelegramListRecipients :item="item" />
      </div>

      <div style="width: 150px">
        получатели: {{ getTotal(item) }}
      </div>
      <div :class="{ 'opacity-5': item.status !== 'sent' }" style="width: 70px">
        <template v-if="item.result">
          <span class="text-secondary">
            {{ getTotal(item, true) }}
          </span>
          / {{ getTotal(item) - getTotal(item, true) }}
        </template>
      </div>
      <div class="text-truncate">
        <RouterLink v-if="item.event" :to="{ name: 'events-id', params: { id: item.event.id } }">
          {{ item.event.name }}
        </RouterLink>
      </div>
    </RouterLink>
  </div>
</template>
