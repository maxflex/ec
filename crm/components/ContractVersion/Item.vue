<script setup lang="ts">
import type { ContractVersionListResource } from '.'
import { mdiTextBoxCheckOutline } from '@mdi/js'

const { item, editable } = defineProps<{
  item: ContractVersionListResource
  editable?: boolean
}>()

const emit = defineEmits<{
  edit: [i: ContractVersionListResource]
}>()
</script>

<template>
  <div :id="`contract-version-${item.id}`">
    <div v-if="editable" class="table-actionss">
      <v-btn
        variant="plain"
        icon="$edit"
        :size="48"
        @click="emit('edit', item)"
      />
    </div>
    <div style="width: 210px" class="text-truncate">
      <router-link :to="{ name: 'clients-id', params: { id: item.contract.client.id } }">
        {{ formatName(item.contract.client) }}
      </router-link>
    </div>
    <div style="width: 24px">
      <v-tooltip v-if="item.contract.source" location="bottom" :max-width="400">
        <template #activator="{ props }">
          <v-icon :icon="mdiTextBoxCheckOutline" v-bind="props" />
        </template>
        {{ item.contract.source }}
      </v-tooltip>
    </div>
    <div style="width: 120px">
      <div class="d-flex ga-2 align-center">
        <v-tooltip location="bottom">
          <template #activator="{ props }">
            <UiCircle v-bind="props" :color="item.contract.is_realized ? 'success' : 'border'" />
          </template>
          {{ item.contract.is_realized ? 'реализован' : 'не реализован' }}
        </v-tooltip>
        <span>
          №{{ item.contract.id }}-{{ item.seq }}
        </span>
      </div>
    </div>
    <div style="width: 80px">
      {{ formatDate(item.date) }}
    </div>
    <div style="width: 120px">
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
    <div style="width: 110px">
      <ContractVersionSumChange :item="item" />
    </div>
  </div>
</template>
