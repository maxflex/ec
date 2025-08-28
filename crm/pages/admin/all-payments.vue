<script setup lang="ts">
import type { ContractPaymentDialog, OtherPaymentDialog } from '#build/components'
import type { AllPaymentResource } from '~/components/OtherPayment'

interface Filters {
  contract_id?: number
  company?: Company
  method: ContractPaymentMethod[]
  is_confirmed?: number
}

const otherPaymentDialog = ref<InstanceType<typeof OtherPaymentDialog>>()
const contractPaymentDialog = ref<InstanceType<typeof ContractPaymentDialog>>()
const filters = ref<Filters>(loadFilters({
  year: [],
  method: [],
}))

const { items, indexPageData } = useIndex<AllPaymentResource>(
  `all-payments`,
  filters,
)

function edit(item: AllPaymentResource) {
  item.contract_id
    ? contractPaymentDialog.value?.edit(item.id)
    : otherPaymentDialog.value?.edit(item.id)
}

function onUpdated(item: AllPaymentResource) {
  const index = items.value.findIndex(e => e.id === item.id && !e.contract_id)
  index === -1
    ? items.value.unshift(item)
    : items.value.splice(index, 1, item)
  itemUpdated('other-payment', item.id)
}

function onDeleted(id: number) {
  const index = items.value.findIndex(e => e.id === id && !e.contract_id)
  if (index !== -1) {
    items.value.splice(index, 1)
  }
}

function getId(item: AllPaymentResource) {
  return `${item.contract_id ? 'contract-payment' : 'other-payment'}-${item.id}`
}
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <UiClearableSelect
        v-model="filters.contract_id"
        label="Тип"
        :items="yesNo('платежи по договору', 'прочие платежи')"
        density="comfortable"
      />
      <UiMultipleSelect
        v-model="filters.method"
        label="Метод"
        :items="selectItems(ContractPaymentMethodLabel)"
        density="comfortable"
      />
      <UiClearableSelect
        v-model="filters.company"
        label="Компания"
        :items="selectItems(CompanyLabel)"
        density="comfortable"
      />
      <UiClearableSelect
        v-model="filters.is_confirmed"
        label="Подтверждение"
        :items="yesNo()"
        density="comfortable"
      />
    </template>
    <template #buttons>
      <v-btn color="primary" @click="otherPaymentDialog?.create()">
        добавить платеж
      </v-btn>
    </template>
    <div class="table">
      <div v-for="item in items" :id="getId(item)" :key="getId(item)">
        <div class="table-actionss">
          <v-btn
            icon="$edit"
            :size="48"
            variant="plain"
            @click="edit(item)"
          />
        </div>
        <div style="width: 230px">
          <RouterLink v-if="item.client_id" :to="{ name: 'clients-id', params: { id: item.client_id } }">
            {{ formatName(item) }}
          </RouterLink>
          <span v-else>
            {{ formatName(item) }}
          </span>
        </div>
        <div style="width: 120px">
          {{ formatDate(item.date) }}
        </div>
        <div style="width: 140px" :class="{ 'text-error': item.is_return }">
          {{ formatPrice(item.sum) }} руб.
        </div>
        <div style="width: 120px">
          {{ ContractPaymentMethodLabel[item.method] }}
          <div v-if="item.pko_number" class="text-gray text-caption">
            ПКО: {{ item.pko_number }}
          </div>
        </div>
        <div style="width: 90px">
          {{ CompanyLabel[item.company] }}
        </div>
        <div style="flex: 1" class="text-truncate">
          <span v-if="item.contract_id">
            договор №{{ item.contract_id }}
          </span>
          <span v-else>
            {{ item.purpose }}
          </span>
        </div>
        <div style="width: 140px; flex: initial">
          <span v-if="item.is_confirmed" class="text-success">
            подтверждён
          </span>
          <span v-else class="text-gray">
            не подтверждён
          </span>
        </div>
      </div>
    </div>
  </UiIndexPage>
  <OtherPaymentDialog ref="otherPaymentDialog" @updated="onUpdated" @deleted="onDeleted" />
  <ContractPaymentDialog ref="contractPaymentDialog" />
</template>

<style lang="scss">
.page-all-payments {
  .filters__inputs {
    & > div {
      width: 230px !important;
    }
  }
}
</style>
