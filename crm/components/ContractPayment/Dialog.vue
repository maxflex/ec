<script setup lang="ts">
import type { PrintDialog } from '#components'
import type { ContractPaymentResource } from '.'
import type { ContractResource } from '../ContractVersion'
import { cloneDeep } from 'lodash-es'
import { ContractPaymentMethodLabel } from '~/utils/labels'
import { apiUrl, modelDefaults, printOptions } from '.'
import { updateMenuCounts } from '../Menu'

const emit = defineEmits<{
  updated: [e: ContractPaymentResource]
  deleted: [e: ContractPaymentResource]
}>()

const cardNumberMask = { mask: '#∗∗∗ ∗∗∗∗ ∗∗∗∗ ####' }
const sumInput = ref()
const { width, dialog } = useDialog('default')
const saving = ref(false)
const loading = ref(false)
const itemId = ref<number>()
// был синхронизирован на момент открытия диалога
const was1cSynced = ref(false)
// был отправлен чпек
const wasReceiptSent = ref(false)

const printDialog = ref<InstanceType<typeof PrintDialog>>()
const item = ref<ContractPaymentResource>(modelDefaults)

function create(c: ContractResource) {
  was1cSynced.value = false
  wasReceiptSent.value = false
  itemId.value = undefined
  item.value = cloneDeep(modelDefaults)
  item.value.contract = cloneDeep(c)
  item.value.contract_id = c.id
  dialog.value = true
  nextTick(() => sumInput.value?.focus())
}

async function edit(id: number) {
  itemId.value = id
  loading.value = true
  dialog.value = true
  const { data } = await useHttp<ContractPaymentResource>(`${apiUrl}/${id}`)
  if (data.value) {
    item.value = data.value
    was1cSynced.value = data.value.is_1c_synced
    wasReceiptSent.value = !!data.value.receipt_number
  }
  loading.value = false
}

async function save() {
  saving.value = true
  const method = itemId.value ? `put` : `post`
  const url = itemId.value ? `${apiUrl}/${itemId.value}` : apiUrl
  const { data, error } = await useHttp<ContractPaymentResource>(url, {
    method,
    body: item.value,
  })
  if (error.value) {
    useGlobalMessage(`Выберите, куда отправить чек`, 'error')
    saving.value = false

    return
  }
  if (data.value) {
    emit('updated', data.value)
  }
  if (!itemId.value) {
    useGlobalMessage(`Создан платеж к договору №${item.value.contract_id}`, 'success')
    // если создали из Альфа-Платежа, обновить счетчики
    if (item.value.external_id) {
      updateMenuCounts()
    }
  }
  dialog.value = false
  setTimeout(() => saving.value = false, 300)
}

function onDeleted() {
  dialog.value = false
  emit('deleted', item.value)
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
          <div class="dialog-subheader">
            к договору №{{ item.contract_id }}
          </div>
        </span>
        <div>
          <template v-if="itemId">
            <CrudDeleteBtn
              v-if="!wasReceiptSent"
              :id="itemId"
              :api-url="apiUrl"
              confirm-text="Вы уверены, что хотите удалить платеж?"
              @deleted="onDeleted()"
            />
            <v-menu>
              <template #activator="{ props }">
                <v-btn
                  v-bind="props"
                  icon="$print"
                  :size="48"
                  variant="text"
                />
              </template>
              <v-list>
                <v-list-item
                  v-for="p in printOptions" :key="p.id"
                  @click="printDialog?.open(p, { contract_payment_id: item.id })"
                >
                  {{ p.label }}
                </v-list-item>
              </v-list>
            </v-menu>
          </template>
          <v-btn
            icon="$save"
            :size="48"
            variant="text"
            :loading="saving"
            @click="save()"
          />
        </div>
      </div>
      <UiLoader v-if="loading" />
      <div v-else class="dialog-body">
        <div>
          <v-text-field
            ref="sumInput"
            v-model="item.sum"
            label="Сумма"
            type="number"
            :disabled="wasReceiptSent"
            hide-spin-buttons
          />
        </div>
        <UiDateInput v-model="item.date" :disabled="wasReceiptSent" today-btn />
        <div>
          <v-select
            v-model="item.method"
            label="Способ оплаты"
            :items="selectItems(ContractPaymentMethodLabel)"
            :disabled="wasReceiptSent"
          />
        </div>
        <div v-if="item.method === 'card'">
          <v-text-field
            v-model="item.card_number"
            v-maska="cardNumberMask"
            placeholder="∗∗∗∗ ∗∗∗∗ ∗∗∗∗ ∗∗∗∗"
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
        <div v-if="!(item.contract.company === 'ooo' || item.method !== 'bill')">
          <v-select v-if="wasReceiptSent" disabled label="Чек отправлен" :model-value="formatPhone(item.receipt_number!)" />
          <ContractPaymentReceiptPhoneSelector
            v-else
            v-model="item.receipt_number"
            :contract-id="item.contract_id"
          />
        </div>
        <div>
          <v-checkbox
            v-model="item.is_confirmed"
            label="Подтверждён"
          />
          <v-checkbox
            v-model="item.is_return"
            :disabled="wasReceiptSent"
            label="Возврат"
          />
          <v-checkbox
            v-model="item.is_1c_synced"
            :disabled="item.method !== 'card' || was1cSynced"
            :label="was1cSynced ? 'Синхронизирован с 1С' : 'Синхронизировать с 1С'"
          />
        </div>
      </div>
    </div>
  </v-dialog>
  <LazyPrintDialog ref="printDialog" />
</template>
