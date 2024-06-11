<script setup lang="ts">
import { mdiEmailOutline, mdiHistory } from '@mdi/js'

const { items } = defineProps<{
  items: PhoneListResource[]
}>()
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
        <v-icon :icon="mdiEmailOutline" />
        <v-icon :icon="mdiHistory" />
      </div>
    </div>
  </div>
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
    min-width: 160px;
  }
  &__actions {
    display: flex;
    gap: 8px;
    flex: 1;
    opacity: 0;
    transition: opacity cubic-bezier(0.4, 0, 0.2, 1) 0.2s;
    .v-icon {
      top: -2px;
      font-size: 18px;
      color: rgb(var(--v-theme-gray));
    }
  }
}
</style>
