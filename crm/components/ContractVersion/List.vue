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
        №{{ item.contract.id }}-{{ item.version }}
      </div>
      <div style="width: 140px">
        от {{ formatDate(item.date) }}
      </div>
      <div style="width: 160px">
        {{ YearLabel[item.contract.year] }}
      </div>
      <div style="width: 150px">
        <span v-if="item.programs.length === 0" class="text-gray"> нет программ </span>
        <template v-else>
          программы:
          <span class="contract-programs">
            <span v-if="item.programs.some((e) => !e.is_closed)">
              {{ item.programs.filter((e) => !e.is_closed).length }}
            </span>
            <span v-if="item.programs.some((e) => e.is_closed)" class="text-error">
              {{ item.programs.filter((e) => e.is_closed).length }}
            </span>
          </span>
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
