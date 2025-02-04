<script setup lang="ts">
import { mdiAlertCircleOutline, mdiCheckAll } from '@mdi/js'

const { items } = defineProps<{
  items: TelegramMessageResource[]
}>()
</script>

<template>
  <div class="telegram-message-history">
    <div v-for="m in items" :key="m.id" class="telegram-message-history__message">
      <div class="telegram-message-history__title">
        {{ formatDateTime(m.created_at) }}
        <v-icon v-if="m.telegram_id" color="success" :icon="mdiCheckAll" :size="14" />
        <v-icon v-else color="error" :icon="mdiAlertCircleOutline" :size="14" />
      </div>
      <div class="telegram-message-history__text" v-html="m.text" />
    </div>
  </div>
</template>

<style lang="scss">
.telegram-message-history {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  gap: 40px;
  padding: 20px;
  & > div {
    margin-bottom: 16px;
  }
  &__title {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 14px;
    margin-bottom: 20px;
    color: rgb(var(--v-theme-gray));
    font-weight: 500;
  }
  &__text {
    white-space: pre-line;
  }
}
</style>
