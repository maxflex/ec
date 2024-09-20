<script setup lang="ts">
import type { TelegramMessageBulkDialog } from '#build/components'

const filters = ref<TelegramMessageFilters>(loadFilters({}))
const telegramMessageBulkDialog = ref<InstanceType<typeof TelegramMessageBulkDialog>>()

const { items, indexPageData, reloadData } = useIndex<TelegramMessageResource, TelegramMessageFilters>(
    `telegram-messages`,
    filters,
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <TelegramMessageFilters v-model="filters" />
    </template>
    <template #buttons>
      <v-btn
        append-icon="$next"
        color="primary"
        @click="telegramMessageBulkDialog?.open()"
      >
        групповая отправка
      </v-btn>
    </template>
    <TelegramMessageList :items="items" />
  </UiIndexPage>
  <TelegramMessageBulkDialog ref="telegramMessageBulkDialog" @updated="reloadData" />
</template>
