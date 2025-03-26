<script setup lang="ts">
import { mdiEye } from '@mdi/js'

const { items } = defineProps<{
  items: TelegramMessageResource[]
}>()
const readDialog = ref()
</script>

<template>
  <div class="table telegram-message-list">
    <div v-for="m in items" :key="m.id">
      <div class="telegram-message-list__avatar-name">
        <UiPerson :item="m.entity" />
      </div>
      <div style="width: 170px">
        <span :class="m.telegram_id ? 'text-secondary' : 'text-black'">
          {{ formatPhone(m.number) }}
        </span>
      </div>
      <div style="flex: 1" class="text-truncate relative">
        {{ m.text }}
      </div>
      <div v-if="m.list_id" style="width: 110px">
        <RouterLink :to="{ name: 'telegram-lists-id', params: { id: m.list_id } }">
          рассылка {{ m.list_id }}
        </RouterLink>
      </div>
      <div style="width: 40px">
        <v-btn :icon="mdiEye" :size="40" variant="plain" @click="readDialog.open(m.text)" />
      </div>
      <div v-if="m.template" style="width: 170px">
        <v-chip class="text-deepOrange">
          {{ TelegramTemplateLabel[m.template] }}
        </v-chip>
      </div>
      <div style="flex: initial; width: 140px" class="text-gray">
        {{ formatDateTime(m.created_at) }}
      </div>
    </div>
  </div>
  <TelegramMessageReadDialog ref="readDialog" />
</template>

<style lang="scss">
.telegram-message-list {
  &__avatar-name {
    width: 200px;
    display: inline-flex;
    align-items: center;
    .v-avatar {
      margin-right: 10px;
    }
  }
}
</style>
