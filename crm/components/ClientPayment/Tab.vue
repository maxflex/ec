<script setup lang="ts">
import type { ClientPaymentDialog } from '#build/components'

const { clientId } = defineProps<{ clientId: number }>()

const tabName = 'ClientPaymentTab'

const filters = ref<YearFilters>(loadFilters({
  year: currentAcademicYear(),
}, tabName))

const loading = ref(true)
const items = ref<ClientPaymentResource[]>([])
const clientPaymentDialog = ref<InstanceType<typeof ClientPaymentDialog>>()

async function loadData() {
  loading.value = true
  const { data } = await useHttp<ApiResponse<ClientPaymentResource>>(
    'client-payments',
    {
      params: {
        ...filters.value,
        client_id: clientId,
      },
    },
  )
  if (data.value) {
    const { data: newItems } = data.value
    items.value = newItems
  }
  loading.value = false
}

watch(filters, (newVal) => {
  saveFilters(newVal, tabName)
  loadData()
}, { deep: true })

function onUpdated(p: ClientPaymentResource) {
  const index = items.value.findIndex(e => e.id === p.id)
  if (index !== -1) {
    items.value[index] = p
  }
  else {
    items.value.unshift(p)
  }
  itemUpdated('client-payment', p.id)
}

function onDeleted(p: ClientPaymentResource) {
  const index = items.value.findIndex(e => e.id === p.id)
  if (index !== -1) {
    items.value.splice(index, 1)
  }
}

const noData = computed(() => !loading.value && items.value.length === 0)

nextTick(loadData)
</script>

<template>
  <UiIndexPage :data="{ loading, noData }">
    <template #filters>
      <v-select
        v-model="filters.year" :items="selectItems(YearLabel)" label="Год"
        density="comfortable"
      />
    </template>
    <template #buttons>
      <v-btn
        color="primary"
        @click="clientPaymentDialog?.create(clientId, filters.year)"
      >
        добавить платеж
      </v-btn>
    </template>
    <ClientPaymentList :items="items" @edit="clientPaymentDialog?.edit" />
  </UiIndexPage>
  <ClientPaymentDialog
    ref="clientPaymentDialog"
    @updated="onUpdated"
    @deleted="onDeleted"
  />
</template>
