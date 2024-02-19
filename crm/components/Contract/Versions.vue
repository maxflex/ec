<script setup lang="ts">
import type { ContractVersion, ContractVersions } from "~/utils/models"
import { PROGRAM } from "~/utils/sment"

const emit = defineEmits<{
  (e: "open", val: ContractVersion): void
}>()

const { versions } = defineProps<{
  versions: ContractVersions
}>()
</script>

<template>
  <table>
    <tr v-for="version in versions">
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
        <div class="client-contracts__subjects text-truncate">
          <div v-for="p in version.programs" :key="p.id">
            <span :class="{ 'text-error': p.is_closed }">
              {{ PROGRAM[p.program] }}
            </span>
            <span class="text-grey">
              {{ p.lessons }}
            </span>
          </div>
        </div>
      </td>
      <td width="50" class="text-right">
        <v-btn icon :size="48" @click="emit('open', version)">
          <v-icon> mdi-dots-horizontal </v-icon>
        </v-btn>
      </td>
    </tr>
  </table>
</template>
