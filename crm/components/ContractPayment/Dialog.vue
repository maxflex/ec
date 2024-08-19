<script setup lang="ts">
import { clone } from 'rambda'

const emit = defineEmits<{
  updated: [e: ContractPaymentResource]
  deleted: [e: ContractPaymentResource]
}>()
const { width, dialog } = useDialog('default')
const deleting = ref(false)
const saving = ref(false)
const loading = ref(false)
const itemId = ref<number>()

const modelDefaults: ContractPaymentResource = {
  id: newId(),
  sum: 0,
  date: today(),
  method: 'card',
  is_confirmed: false,
  is_return: false,
  contract_id: newId(),
  pko_number: null,
  card_number: null,
}
const item = ref<ContractPaymentResource>(modelDefaults)

function create(c: ContractResource) {
  itemId.value = undefined
  item.value = clone(modelDefaults)
  item.value.contract_id = c.id
  dialog.value = true
}

async function edit(e: ContractPaymentResource) {
  const { id } = e
  itemId.value = id
  loading.value = true
  dialog.value = true
  const { data } = await useHttp<ContractPaymentResource>(`contract-payments/${id}`)
  if (data.value) {
    item.value = data.value
  }
  loading.value = false
}

async function save() {
  saving.value = true
  const method = itemId.value ? `put` : `post`
  const url = itemId.value ? `contract-payments/${itemId.value}` : `contract-payments`
  const { data } = await useHttp<ContractPaymentResource>(url, {
    method,
    body: item.value,
  })
  if (data.value) {
    emit('updated', data.value)
  }
  dialog.value = false
  setTimeout(() => saving.value = false, 300)
}

async function destroy() {
  if (!confirm('Вы уверены, что хотите удалить платеж?')) {
    return
  }
  deleting.value = true
  const { data, status } = await useHttp(`contract-payments/${item.value.id}`, {
    method: 'delete',
  })
  if (status.value === 'error') {
    deleting.value = false
  }
  else if (data.value) {
    emit('deleted', item.value)
    dialog.value = false
    setTimeout(() => deleting.value = false, 300)
  }
}

defineExpose({ create, edit })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-wrapper">
      <div class="dialog-header">
        <span v-if="itemId">
          Редактировать платеж
          <div class="dialog-subheader">
            <template v-if="item.user && item.created_at">
              {{ formatName(item.user) }}
              {{ formatDateTime(item.created_at) }}
            </template>
          </div>
        </span>
        <span v-else>
          Новый платеж
        </span>
        <div>
          <v-btn
            v-if="itemId"
            icon="$delete"
            :size="48"
            variant="text"
            :loading="deleting"
            class="remove-btn"
            @click="destroy()"
          />
          <v-btn
            icon="$save"
            :size="48"
            variant="text"
            :loading="saving"
            @click="save()"
          />
        </div>
      </div>
      <UiLoaderr v-if="loading" />
      <div v-else class="dialog-body">
        <div>
          <v-text-field
            v-model="item.sum"
            label="Сумма"
            type="number"
            hide-spin-buttons
          />
        </div>
        <UiDateInput v-model="item.date" today-btn />
        <div>
          <v-select
            v-model="item.method"
            label="Способ оплаты"
            :items="selectItems(ClientPaymentMethodLabel)"
          />
        </div>
        <div v-if="item.method === 'card'">
          <v-text-field
            v-model="item.card_number"
            label="Номер карты"
          />
        </div>
        <div v-else-if="item.method === 'cash'">
          <v-text-field
            v-if="itemId"
            v-model="item.pko_number"
            label="Номер ПКО"
            type="number"
            hide-spin-buttons
          />
          <v-text-field v-else disabled model-value="Будет присвоен" label="Номер ПКО" />
        </div>
        <div>
          <v-checkbox
            v-model="item.is_confirmed"
            label="Подтверждён"
          />
          <v-checkbox
            v-model="item.is_return"
            label="Возврат"
          />
        </div>
      </div>
    </div>
  </v-dialog>
</template>
