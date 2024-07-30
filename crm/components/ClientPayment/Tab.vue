<script setup lang="ts">
import type { ClientPaymentDialog } from '#build/components'

const { clientId } = defineProps<{
  clientId: number
}>()
const filters = ref<{
  year: Year
}>({
  year: currentAcademicYear(),
})
const loading = ref(false)
const items = ref<ClientPaymentResource[]>([])
const clientPaymentDialog = ref<InstanceType<typeof ClientPaymentDialog>>()

async function loadData() {
  if (loading.value) {
    return
  }
  loading.value = true
  const { data } = await useHttp<ApiResponse<ClientPaymentResource[]>>(
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

watch(filters.value, () => loadData())

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

nextTick(loadData)
</script>

<template>
  <div class="filters">
    <div class="filters-inputs">
      <v-select
        v-model="filters.year" :items="selectItems(YearLabel)" label="Год"
        density="comfortable"
      />
    </div>
    <v-btn
      color="primary"
      @click="clientPaymentDialog?.create(clientId, filters.year)"
    >
      добавить платеж
    </v-btn>
  </div>
  <div>
    <UiLoader3 :loading="loading" />
    <ClientPaymentList :items="items" @open="clientPaymentDialog?.edit" />
  </div>
  <ClientPaymentDialog
    ref="clientPaymentDialog"
    @updated="onUpdated"
    @deleted="onDeleted"
  />
</template>
