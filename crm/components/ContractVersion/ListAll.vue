<!-- В общем списке в меню "Договоры" -->
<script setup lang="ts">
import type { ContractVersionListResource } from '.'
import { mdiTextBoxCheckOutline } from '@mdi/js'

const { items } = defineProps<{
  items: ContractVersionListResource[]
}>()
const emit = defineEmits<{
  edit: [i: ContractVersionListResource]
}>()
</script>

<template>
  <div class="table table--padding flex-start">
    <div
      v-for="item in items"
      :id="`contract-version-${item.id}`"
      :key="item.id"
    >
      <div class="table-actionss">
        <v-btn
          variant="plain"
          icon="$edit"
          :size="48"
          @click="emit('edit', item)"
        />
      </div>
      <div style="width: 230px">
        <router-link
          :to="{ name: 'clients-id', params: { id: item.contract.client.id } }"
        >
          {{ formatName(item.contract.client) }}
        </router-link>
      </div>
      <div style="width: 130px">
        <div class="d-flex ga-2 align-center">
          <span>
            №{{ item.contract.id }}-{{ item.seq }}
          </span>
          <v-tooltip v-if="item.contract.source" location="bottom">
            <template #activator="{ props }">
              <v-icon :icon="mdiTextBoxCheckOutline" :size="18" color="primary" v-bind="props" />
            </template>
            {{ item.contract.source }}
          </v-tooltip>
        </div>
      </div>
      <div style="width: 100px">
        {{ formatDate(item.date) }}
      </div>
      <div style="width: 140px">
        <div v-for="(value, d) in item.direction_counts" :key="d">
          {{ DirectionLabel[d] }} / {{ value }}
        </div>
      </div>
      <div style="width: 120px">
        <span v-if="!item.payments_count" class="text-gray">
          платежей нет
        </span>
        <span v-else> платежей: {{ item.payments_count }} </span>
      </div>

      <div style="width: 100px">
        <span v-if="item.price_avg === 0" class="text-gray">
          цен нет
        </span>
        <span v-else> {{ item.price_avg }} руб. </span>
      </div>
      <div style="width: 120px">
        <UiIfSet :value="item.sum">
          <template #empty>
            без суммы
          </template>
          {{ formatPrice(item.sum) }} руб.
        </UiIfSet>
      </div>
      <div>
        <ContractVersionSumChange :item="item" />
      </div>
    </div>
  </div>
</template>
