<script setup lang="ts">
import type { PrintDialog } from '#components'
import type { ContractPaymentResource } from '.'
import { clone } from 'lodash-es'
import { ContractPaymentMethodLabel } from '~/utils/labels'
import { apiUrl, modelDefaults, printOptions } from '.'

const emit = defineEmits<{
  updated: [e: ContractPaymentResource]
  deleted: [e: ContractPaymentResource]
}>()

const cardNumberMask = { mask: '#∗∗∗ ∗∗∗∗ ∗∗∗∗ ####' }

const { width, dialog } = useDialog('default')
const saving = ref(false)
const loading = ref(false)
const itemId = ref<number>()

const printDialog = ref<InstanceType<typeof PrintDialog>>()
const item = ref<ContractPaymentResource>(modelDefaults)

function create(c: ContractResource) {
  itemId.value = undefined
  item.value = clone(modelDefaults)
  item.value.contract_id = c.id
  dialog.value = true
}

async function edit(id: number) {
  itemId.value = id
  loading.value = true
  dialog.value = true
  const { data } = await useHttp<ContractPaymentResource>(`${apiUrl}/${id}`)
  if (data.value) {
    item.value = data.value
  }
  loading.value = false
}

async function save() {
  saving.value = true
  const method = itemId.value ? `put` : `post`
  const url = itemId.value ? `${apiUrl}/${itemId.value}` : apiUrl
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
        </span>
        <div>
          <template
            v-if="itemId"
          >
            <DialogDeleteBtn
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
            :items="selectItems(ContractPaymentMethodLabel)"
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
  <LazyPrintDialog ref="printDialog" />
</template>
