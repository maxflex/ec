<!-- В общем списке в меню "Договоры" -->
<script setup lang="ts">
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
      <div style="width: 250px">
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
          <v-icon v-if="item.contract.source" :icon="mdiTextBoxCheckOutline" :size="18" color="primary" />
        </div>
      </div>
      <div style="width: 140px">
        от {{ formatDate(item.date) }}
      </div>
      <div style="width: 160px">
        {{ YearLabel[item.contract.year] }}
      </div>
      <div style="width: 150px">
        <div v-for="(value, d) in item.direction_counts" :key="d">
          {{ DirectionLabel[d] }} / {{ value }}
        </div>
      </div>
      <div style="width: 150px">
        <span v-if="!item.payments_count" class="text-gray">
          платежей нет
        </span>
        <span v-else> платежей: {{ item.payments_count }} </span>
      </div>
      <div>{{ formatPrice(item.sum) }} руб.</div>
    </div>
  </div>
</template>
