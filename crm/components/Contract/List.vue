<script setup lang="ts">
import type { Contract, ContractVersion } from "~/utils/models"
import { PROGRAM } from "~/utils/sment"

const emit = defineEmits<{
  (e: "open", contract: Contract, version: ContractVersion): void
}>()

const { contract } = defineProps<{
  contract: Contract
}>()
</script>

<template>
  <table class="contract-list">
    <tr v-for="version in contract.versions">
      <td width="150">версия {{ version.version }}</td>
      <td width="220">от {{ formatDate(version.date) }}</td>
      <td width="220">{{ version.sum }} руб.</td>
      <td width="220">
        <span v-if="version.payments.length === 0" class="text-grey">
          платежей нет
        </span>
        <template v-else> {{ version.payments.length }} платежей </template>
      </td>
      <td>
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
          <div v-if="version.programs.length > 3" class="text-gray">
            ... ещё {{ version.programs.length - 2 }}
          </div>
        </div>
      </td>
      <td width="50" class="text-right">
        <v-btn icon :size="48" @click="emit('open', contract, version)">
          <v-icon icon="$more"></v-icon>
        </v-btn>
      </td>
    </tr>
  </table>
</template>

<style lang="scss">
.contract-list {
  table-layout: fixed;
  border-collapse: collapse;
  border-spacing: 0;
  // width: 100%;
  left: -20px;
  position: relative;
  width: calc(100% + 40px);
  tr {
    vertical-align: top;
    td {
      border-bottom: thin solid
        rgba(var(--v-border-color), var(--v-border-opacity));
      padding: 16px 16px;
      &:first-child {
        padding-left: 20px;
      }
      &:last-child {
        padding-right: 20px;
        position: relative;
        button {
          position: absolute;
          right: 20px;
          top: 4px;
        }
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
