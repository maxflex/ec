<!-- Список договоров в клиенте -->
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
    >
      <div class="table-actionss">
        <v-btn
          icon="$edit"
          :size="48"
          variant="plain"
          @click="emit('edit', version)"
        />
      </div>
      <div width="150">
        версия {{ version.version }}
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
        <span v-if="version.programs.length === 0" class="text-gray"> нет программ </span>
        <template v-else>
          программы:
          <span class="contract-programs">
            <span v-if="version.programs.some((e) => !e.is_closed)">
              {{ version.programs.filter((e) => !e.is_closed).length }}
            </span>

            <span
              v-if="version.programs.some((e) => e.is_closed)"
              class="text-error"
            >
              {{ version.programs.filter((e) => e.is_closed).length }}
            </span>
          </span>
        </template>
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
</style>
