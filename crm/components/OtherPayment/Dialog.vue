<script setup lang="ts">
import type { AllPaymentsResource, OtherPaymentResource } from '.'
import { cloneDeep } from 'lodash-es'
import { apiUrl, modelDefaults, OtherPaymentMethodLabel } from '.'

const emit = defineEmits<{
  updated: [e: AllPaymentsResource]
  deleted: [id: number]
}>()

const { width, dialog } = useDialog('default')
const saving = ref(false)
const loading = ref(false)
const itemId = ref<number>()
const item = ref<OtherPaymentResource>(cloneDeep(modelDefaults))
const phoneMask = { mask: '+7 (###) ###-##-##' }
const disabled = computed<boolean>(() => !!itemId.value)

function create() {
  itemId.value = undefined
  item.value = cloneDeep(modelDefaults)
  dialog.value = true
}

async function edit(id: number) {
  itemId.value = id
  loading.value = true
  dialog.value = true
  const { data } = await useHttp<OtherPaymentResource>(`${apiUrl}/${id}`)
  item.value = data.value!
  loading.value = false
}

async function save() {
  saving.value = true
  const method = itemId.value ? `put` : `post`
  const url = itemId.value ? `${apiUrl}/${itemId.value}` : apiUrl
  const { data, error } = await useHttp<AllPaymentsResource>(url, {
    method,
    body: item.value,
  })
  if (error.value) {
    useGlobalMessage(`Выберите, куда отправить чек`, 'error')
    saving.value = false

    return
  }
  emit('updated', data.value!)
  dialog.value = false
  setTimeout(() => saving.value = false, 300)
}

// async function destroy() {
//   useGlobalMessage('Платеж удален', 'success')
//   emit('deleted', itemId.value!)
//   dialog.value = false
// }

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
          Добавить платеж
        </span>
        <div>
          <template v-if="itemId">
            <PrintBtn :items="[9, 14]" :extra="{ other_payment_id: itemId }" />
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
            :disabled="disabled"
          />
        </div>
        <UiDateInput v-model="item.date" today-btn :disabled="disabled" />
        <div>
          <v-select
            v-model="item.method"
            label="Способ оплаты"
            :items="selectItems(OtherPaymentMethodLabel)"
            :disabled="disabled"
          />
        </div>
        <div v-if="item.method === 'card'">
          <v-text-field
            v-model="item.card_number"
            v-maska="{ mask: '#####' }"
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
          <v-text-field v-else disabled label="Будет присвоен номер ПКО" />
        </div>

        <div>
          <v-text-field
            v-model="item.last_name"
            label="Фамилия"
            :disabled="disabled"
          />
        </div>
        <div>
          <v-text-field
            v-model="item.first_name"
            label="Имя"
            :disabled="disabled"
          />
        </div>
        <div>
          <v-text-field
            v-model="item.middle_name"
            label="Отчество"
            :disabled="disabled"
          />
        </div>
        <v-text-field
          v-model="item.receipt_number"
          v-maska="phoneMask"
          :disabled="disabled"
          :label="itemId && item.receipt_number ? 'Чек отправлен' : 'Отправить чек'"
        />
        <div>
          <v-textarea
            v-model="item.purpose"
            label="Назначение"
            :disabled="disabled"
            no-resize
            rows="3"
          />
        </div>
        <div>
          <v-checkbox
            v-model="item.is_confirmed"
            label="Подтверждён"
          />
          <v-checkbox
            v-model="item.is_return"
            :disabled="disabled"
            label="Возврат"
          />
        </div>
      </div>
    </div>
  </v-dialog>
</template>
