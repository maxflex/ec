<script setup lang="ts">
import { mdiEye } from '@mdi/js'

const { items } = defineProps<{
  items: TelegramMessageResource[]
}>()
const readDialog = ref()
</script>

<template>
  <Table class="telegram-message-list">
    <TableRow v-for="m in items" :key="m.id">
      <div class="table-actionss">
        <v-btn :icon="mdiEye" :size="40" variant="plain" @click="readDialog.open(m.text)" />
      </div>
      <TableCol class="telegram-message-list__avatar-name">
        <UiPerson :item="m.entity" />
      </TableCol>
      <TableCol :width="170">
        <span :class="m.telegram_id ? 'text-secondary' : 'text-black'">
          {{ formatPhone(m.number) }}
        </span>
      </TableCol>
      <TableCol class="text-truncate relative">
        {{ m.text }}
      </TableCol>
      <TableCol :width="150">
        <RouterLink v-if="m.list_id" :to="{ name: 'telegram-lists-id', params: { id: m.list_id } }">
          рассылка {{ m.list_id }}
        </RouterLink>
        <span v-else-if="m.template" class="text-gray">
          {{ TelegramTemplateLabel[m.template] }}
        </span>
      </TableCol>
      <TableCol style="width: 140px; flex: initial" class="text-gray">
        {{ formatDateTime(m.created_at) }}
      </TableCol>
    </TableRow>
  </Table>
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
