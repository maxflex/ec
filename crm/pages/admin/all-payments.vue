<script setup lang="ts">
import type { ClientPaymentDialog, ContractPaymentDialog } from '#build/components'

interface Filters {
  year: Year
  method?: ContractPaymentMethod
  company?: Company
  is_confirmed?: number
}

const clientPaymentDialog = ref<InstanceType<typeof ClientPaymentDialog>>()
const contractPaymentDialog = ref<InstanceType<typeof ContractPaymentDialog>>()
const filters = ref<Filters>(loadFilters({
  year: currentAcademicYear(),
}))

const { items, indexPageData } = useIndex<AllPaymentResource, Filters>(
  `all-payments`,
  filters,
)

function edit(item: AllPaymentResource) {
  item.contract_id
    ? contractPaymentDialog.value?.edit(item.id)
    : clientPaymentDialog.value?.edit(item.id)
}
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <v-select
        v-model="filters.year"
        label="Учебный год"
        :items="selectItems(YearLabel)"
        density="comfortable"
      />
      <UiClearableSelect
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
        label="Подтверждён"
        :items="yesNo()"
        density="comfortable"
      />
    </template>
    <div class="table">
      <div v-for="item in items" :key="(item.contract_id ? 'c' : 'p') + item.id">
        <div class="table-actionss">
          <v-btn
            icon="$edit"
            :size="48"
            variant="plain"
            @click="edit(item)"
          />
        </div>
        <div style="width: 250px">
          <UiPerson :item="item.client" />
        </div>
        <div style="width: 130px">
          {{ formatDate(item.date) }}
        </div>
        <div style="width: 150px" :class="{ 'text-error': item.is_return }">
          {{ formatPrice(item.sum) }} руб.
        </div>
        <div style="width: 120px">
          {{ ContractPaymentMethodLabel[item.method] }}
        </div>
        <div style="width: 90px">
          {{ CompanyLabel[item.company] }}
        </div>
        <div style="flex: 1">
          {{ item.purpose ?? `договор ${item.contract_id}` }}
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
  <ClientPaymentDialog ref="clientPaymentDialog" />
  <ContractPaymentDialog ref="contractPaymentDialog" />
</template>
