<script setup lang="ts">
import type { TelegramMessageBulkDialog } from '#build/components'
import type { Filters } from '~/components/TelegramMessage/Filters.vue'

const telegramMessageBulkDialog = ref<InstanceType<typeof TelegramMessageBulkDialog>>()

const { items, loading, onFiltersApply, reloadData } = useIndex<GradeListResource, Filters>(`telegram-messages`)
</script>

<template>
  <div class="filters">
    <TelegramMessageFilters @apply="onFiltersApply" />
    <v-spacer />
    <v-btn
      append-icon="$next"
      color="primary"
      @click="telegramMessageBulkDialog?.open()"
    >
      групповая отправка
    </v-btn>
  </div>
  <div>
    <UiLoader3 :loading="loading" />
    <TelegramMessageList :items="items" />
  </div>
  <TelegramMessageBulkDialog ref="telegramMessageBulkDialog" @updated="reloadData" />
</template>
