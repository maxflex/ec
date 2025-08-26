<script setup lang="ts">
import type { PrintDialog } from '#components'
import type { PrintOption, PrintOptionId } from '.'
import { printOptions } from '.'

type Item = PrintOptionId | Partial<Record<PrintOptionId, Company>>

const { items, extra = {}, variant = 'text' } = defineProps<{
  items: Item[]
  extra?: object
  variant?: 'text' | 'plain'
}>()

const options: PrintOption[] = (function () {
  const result: PrintOption[] = []

  for (const i of items) {
    if (typeof i === 'number' && i in printOptions) {
      result.push({
        id: i,
        label: printOptions[i], // ok: p is number here
      })
    }
    else if (i && typeof i === 'object') {
      for (const [k, company] of Object.entries(i)) {
        const id = Number(k) as PrintOptionId
        if (Number.isFinite(id) && id in printOptions) {
          result.push({
            id,
            label: `${printOptions[id]} (${CompanyLabel[company]})`,
            company,
          })
        }
      }
    }
  }

  return result
})()

const printDialog = ref<InstanceType<typeof PrintDialog>>()
</script>

<template>
  <v-menu>
    <template #activator="{ props }">
      <v-btn
        v-bind="props"
        icon="$print"
        :size="48"
        :variant="variant"
      />
    </template>
    <v-list>
      <v-list-item
        v-for="option in options"
        :key="option.label"
        @click="printDialog?.open(option, extra)"
      >
        {{ option.label }}
      </v-list-item>
    </v-list>
  </v-menu>
  <LazyPrintDialog ref="printDialog" />
</template>
