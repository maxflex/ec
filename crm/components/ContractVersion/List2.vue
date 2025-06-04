<!-- Вкладка "Договоры" в клиенте -->
<script setup lang="ts">
const { items } = defineProps<{ items: ContractVersionListResource[] }>()
const emit = defineEmits<{
  edit: [cv: ContractVersionListResource]
}>()
</script>

<template>
  <div class="table contract-version-list-2 table--padding">
    <div
      v-for="version in items"
      :id="`contract-version-${version.id}`"
      :key="version.id"
      :class="version.is_active ? 'contract-version--active' : 'contract-version--inactive'"
    >
      <UiTableActions>
        <v-btn
          icon="$edit"
          :size="48"
          variant="plain"
          @click="emit('edit', version)"
        />
</UiTableActions>
      <div width="150">
        версия {{ version.seq }}
      </div>
      <div width="220">
        от {{ formatDate(version.date) }}
      </div>
      <div width="220">
        {{ version.sum }} руб.
      </div>
      <div width="220">
        <span
          v-if="version.payments_count === 0"
          class="text-grey"
        >
          платежей нет
        </span>
        <template v-else>
          {{ version.payments_count }} платежей
        </template>
      </div>
      <div>
        <ContractVersionDirections :item="version" />
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.contract-version-list-2 {
  & > div {
    align-items: flex-start !important;
    & > div {
      &:nth-child(2) {
        width: 150px;
      }
      &:nth-child(3),
      &:nth-child(4),
      &:nth-child(5) {
        width: 220px;
      }
    }
  }
  &__programs {
    display: flex;
    flex-direction: column;
    gap: 10px;
    max-width: 322px;
    & > div {
      display: flex;
      gap: 4px;
    }
  }
}

.contract-version {
  &--active {
    background: rgba(var(--v-theme-primary), 0.3);
  }
  &--inactive {
    & > div {
      opacity: 0.5;
    }
  }
}
</style>
