<script setup lang="ts">
import { mdiFileDocumentArrowRightOutline, mdiFileDocumentRefreshOutline } from '@mdi/js'

const { item } = defineProps<{
  item: {
    is_1c_synced: boolean | null
    receipt_number: string | null
  }
}>()
</script>

<template>
  <div class="contract-payment-icons">
    <div>
      <v-tooltip v-if="item.is_1c_synced" location="bottom">
        <template #activator="{ props }">
          <v-icon :icon="mdiFileDocumentRefreshOutline" v-bind="props" />
        </template>
        Синхронизирован с 1С
      </v-tooltip>
    </div>
    <div>
      <v-tooltip v-if="item.receipt_number" location="bottom">
        <template #activator="{ props }">
          <v-icon :icon="mdiFileDocumentArrowRightOutline" v-bind="props" />
        </template>
        Чек отправлен на {{ formatPhone(item.receipt_number) }}
      </v-tooltip>
    </div>
  </div>
</template>

<style lang="scss">
.contract-payment-icons {
  display: inline-flex;
  align-items: center;
  gap: 10px;
  & > div {
    width: 24px;
  }
}
</style>
