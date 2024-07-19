<script setup lang="ts">
import { mdiSend } from '@mdi/js'
import type { TelegramMessageDialog } from '#build/components'

const { items, person } = defineProps<{
  items: PhoneListResource[]
  person: PersonResource
}>()
const telegramMessageDialog = ref<InstanceType<typeof TelegramMessageDialog>>()
</script>

<template>
  <div
    v-if="items.length"
    class="phone-actions"
  >
    <div
      v-for="p in items"
      :key="p.id"
    >
      <div class="phone-actions__number">
        <a :href="`tel:${p.number}`">
          {{ formatPhone(p.number as string) }}
        </a>
      </div>
      <div class="phone-actions__actions">
        <v-btn
          :icon="mdiSend"
          :size="28"
          variant="text"
          color="secondary"
          :disabled="!p.telegram_id"
          @click="telegramMessageDialog?.open(p, person)"
        />
        <!-- <v-icon :icon="mdiEmailOutline" />
        <v-icon :icon="mdiHistory" /> -->
      </div>
    </div>
  </div>
  <TelegramMessageDialog ref="telegramMessageDialog" />
</template>

<style lang="scss">
.phone-actions {
  margin-top: 2px;
  & > div {
    display: flex;
    flex-wrap: nowrap;
    align-items: center;
    &:hover {
      .phone-actions__actions {
        opacity: 1;
      }
    }
  }
  &__number {
    min-width: 170px;
  }
  &__actions {
    display: flex;
    gap: 8px;
    flex: 1;
    opacity: 0;
    transition: opacity cubic-bezier(0.4, 0, 0.2, 1) 0.2s;
    .v-icon {
      // top: -2px;
      left: 1px;
      font-size: 18px;
      // color: rgb(var(--v-theme-gray));
    }
  }
}
</style>
