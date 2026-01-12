<!-- Вкладка "Договоры" в клиенте -->
<script setup lang="ts">
import type { ContractVersionListResource } from '.'

const { items } = defineProps<{ items: ContractVersionListResource[] }>()
const emit = defineEmits<{
  edit: [cv: ContractVersionListResource]
}>()
</script>

<template>
  <Table class="contract-version-list-2 table--padding">
    <TableRow
      v-for="version in items"
      :id="`contract-version-${version.id}`"
      :key="version.id"
      :class="version.is_active ? 'contract-version--active' : 'contract-version--inactive'"
    >
      <div class="table-actionss">
        <v-btn
          icon="$edit"
          :size="48"
          variant="plain"
          @click="emit('edit', version)"
        />
      </div>
      <TableCol :width="150">
        версия {{ version.seq }}
      </TableCol>
      <TableCol :width="220">
        {{ formatDate(version.date) }}
      </TableCol>
      <TableCol :width="220">
        <span
          v-if="version.payments_count === 0"
          class="text-grey"
        >
          платежей нет
        </span>
        <template v-else>
          {{ version.payments_count }} платежей
        </template>
      </TableCol>
      <TableCol>
        <div v-for="(value, d) in version.direction_counts" :key="d">
          {{ DirectionLabel[d] }} / {{ value }}
        </div>
      </TableCol>
      <TableCol :width="200">
        <UiIfSet :value="version.sum">
          <template #empty>
            без суммы
          </template>
          {{ formatPrice(version.sum) }} руб.
        </UiIfSet>
      </TableCol>
      <TableCol>
        <ContractVersionSumChange :item="version" />
      </TableCol>
    </TableRow>
  </Table>
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
        width: 200px;
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
