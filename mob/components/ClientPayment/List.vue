<script setup lang="ts">
const { items } = defineProps<{ items: ClientPaymentResource[] }>()
const emit = defineEmits<{
  edit: [id: number]
}>()
</script>

<template>
  <div class="table client-payments">
    <div
      v-for="item in items"
      :id="`client-payment-${item.id}`"
      :key="item.id"
    >
      <div class="table-actionss">
        <v-btn
          variant="plain"
          icon="$edit"
          :size="48"
          @click="emit('edit', item.id)"
        />
      </div>
      <div style="width: 200px">
        <NuxtLink :to="{ name: 'clients-id', params: { id: item.client!.id } }">
          {{ formatName(item.client!) }}
        </NuxtLink>
      </div>
      <div style="width: 100px">
        <span
          v-if="item.is_return"
          class="text-error"
        >
          возврат
        </span>
        <span v-else>
          платеж
        </span>
      </div>
      <div style="width: 150px">
        от {{ formatDate(item.date) }}
      </div>
      <div style="width: 150px">
        {{ formatPrice(item.sum) }} руб.
      </div>
      <div style="width: 220px">
        {{ ClientPaymentMethodLabel[item.method] }}
        <div v-if="item.pko_number" class="text-gray text-caption">
          ПКО: {{ item.pko_number }}
        </div>
      </div>
      <div>
        <span v-if="item.is_confirmed" class="text-success">
          подтверждён
        </span>
        <span v-else class="text-gray">
          не подтверждён
        </span>
      </div>
    </div>
  </div>
</template>
