<script setup lang="ts">
import { clone } from 'rambda'

const emit = defineEmits<{
  updated: [e: ClientPaymentResource]
  deleted: [e: ClientPaymentResource]
}>()
const { width, dialog } = useDialog('default')
const deleting = ref(false)
const saving = ref(false)
const loading = ref(false)
const itemId = ref<number>()

const modelDefaults: ClientPaymentResource = {
  id: newId(),
  sum: 0,
  date: today(),
  year: currentAcademicYear(),
  method: 'card',
  company: 'ooo',
  purpose: null,
  is_confirmed: false,
  is_return: false,
}
const item = ref<ClientPaymentResource>(modelDefaults)

function create(clientId: number, year: Year) {
  itemId.value = undefined
  item.value = clone(modelDefaults)
  item.value.year = year
  item.value.client_id = clientId
  dialog.value = true
}

async function edit(e: ClientPaymentResource) {
  const { id } = e
  itemId.value = id
  loading.value = true
  dialog.value = true
  const { data } = await useHttp<ClientPaymentResource>(`client-payments/${id}`)
  if (data.value) {
    item.value = data.value
  }
  loading.value = false
}

async function save() {
  saving.value = true
  const method = itemId.value ? `put` : `post`
  const url = itemId.value ? `client-payments/${itemId.value}` : `client-payments`
  const { data } = await useHttp<ClientPaymentResource>(url, {
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
  const { data, status } = await useHttp(`client-payments/${item.value.id}`, {
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
        <template v-if="itemId">
          Редактировать платеж
        </template>
        <template v-else>
          Новое платеж
        </template>
        <v-btn
          icon="$save"
          :size="48"
          color="#fafafa"
          @click="save()"
        />
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
        <UiDateInput v-model="item.date" />
        <div>
          <v-select v-model="item.year" label="Учебный год" :items="selectItems(YearLabel)" />
        </div>
        <div>
          <v-select
            v-model="item.company"
            label="Компания"
            :items="selectItems(CompanyLabel)"
          />
        </div>
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
          <v-text-field v-else disabled value="Будет присвоен" label="Номер ПКО" />
        </div>
        <div>
          <v-textarea
            v-model="item.purpose"
            label="Назначение"
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
            label="Возврат"
          />
        </div>
        <div v-if="itemId" class="dialog-bottom">
          <span v-if="item.user && item.created_at">
            платеж создан
            {{ formatName(item.user) }}
            {{ formatDateTime(item.created_at) }}
          </span>
          <v-btn
            icon="$delete"
            :size="48"
            color="red"
            variant="plain"
            :loading="deleting"
            @click="destroy()"
          />
        </div>
      </div>
    </div>
  </v-dialog>
</template>
