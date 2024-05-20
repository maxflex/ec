<script setup lang="ts">
import type { Contract, ContractVersion } from '~/utils/models'
import { PROGRAM } from '~/utils/sment'

const emit = defineEmits<{
  (e: 'open', contract: Contract, version: ContractVersion): void
}>()

const { contract } = defineProps<{
  contract: Contract
}>()
</script>

<template>
  <div class="table contract-list table--hover table--padding">
    <div
      v-for="version in contract.versions"
      :key="version.id"
    >
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
          v-if="version.payments.length === 0"
          class="text-grey"
        >
          платежей нет
        </span>
        <template v-else>
          {{ version.payments.length }} платежей
        </template>
      </div>
      <div>
        <div class="contract-list__programs">
          <div
            v-for="p in version.programs.slice(
              0,
              version.programs.length > 3 ? 2 : 3,
            )"
            :key="p.id"
          >
            <span :class="{ 'text-error': p.is_closed }">
              {{ PROGRAM[p.program] }}
            </span>
            <span class="text-grey">
              {{ p.lessons }}
            </span>
          </div>
          <div
            v-if="version.programs.length > 3"
            class="text-gray"
          >
            ... ещё {{ version.programs.length - 2 }}
          </div>
        </div>
      </div>
      <div
        width="50"
        class="table-actions"
      >
        <v-btn
          variant="text"
          icon="$more"
          :size="48"
          @click="emit('open', contract, version)"
        />
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.contract-list {
  & > div {
    align-items: flex-start !important;
    & > div {
      &:nth-child(1) {
        width: 150px;
      }
      &:nth-child(2),
      &:nth-child(3),
      &:nth-child(4) {
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
