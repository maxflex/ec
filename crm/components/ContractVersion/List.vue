<!-- В общем списке в меню "Договоры" -->
<script setup lang="ts">
const { items } = defineProps<{
  items: ContractVersionListResource[]
}>()
const emit = defineEmits<{
  edit: [i: ContractVersionListResource]
}>()
</script>

<template>
  <div class="table">
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
        №{{ item.contract.id }}-{{ item.seq }}
      </div>
      <div style="width: 140px">
        от {{ formatDate(item.date) }}
      </div>
      <div style="width: 160px">
        {{ YearLabel[item.contract.year] }}
      </div>
      <div style="width: 150px">
        <span v-if="item.programs_count === 0" class="text-gray"> программ нет </span>
        <template v-else>
          программ: {{ item.programs_count }}
        </template>
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
