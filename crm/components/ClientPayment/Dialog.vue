<script setup lang="ts">
import { clone } from 'rambda'

const emit = defineEmits<{
  updated: [cp: ClientPaymentResource]
  deleted: [cp: ClientPaymentResource]
}>()
const { dialog, width } = useDialog('default')
const modelDefaults: ClientPaymentResource = {
  id: newId(),
  sum: 0,
  date: today(),
  year: currentAcademicYear(),
  method: 'card',
  entity_type: EntityType.contract,
  entity_id: 0,
  company: 'ooo',
  purpose: null,
  extra: null,
  is_confirmed: false,
  is_return: false,
}
const sumInput = ref()
const saving = ref(false)
const deleting = ref(false)
const item = ref<ClientPaymentResource>(modelDefaults)
const isEditMode = computed(() => item.value.id > 0)

function open(cp: ClientPaymentResource) {
  item.value = clone(cp)
  dialog.value = true
}

function create(c: ContractResource) {
  item.value = {
    ...modelDefaults,
    entity_id: c.id,
    company: c.company,
    year: c.year,
  }
  dialog.value = true
  nextTick(() => {
    sumInput.value.reset()
    sumInput.value.focus()
  })
}

async function save() {
  saving.value = true
  if (isEditMode.value) {
    const { data } = await useHttp<ClientPaymentResource>(`client-payments/${item.value.id}`, {
      method: 'put',
      body: item.value,
    })
    if (data.value) {
      item.value = data.value
    }
  }
  else {
    const { data } = await useHttp<ClientPaymentResource>('client-payments', {
      method: 'post',
      body: item.value,
    })
    if (data.value) {
      item.value = data.value
    }
  }
  nextTick(() => emit('updated', item.value))
  dialog.value = false
  setTimeout(() => saving.value = false, 300)
}

async function destroy() {
  if (!confirm('Вы уверены, что хотите удалить платеж?')) {
    return
  }
  deleting.value = true
  const { status } = await useHttp(`client-payments/${item.value.id}`, {
    method: 'delete',
  })
  if (status.value === 'error') {
    deleting.value = false
  }
  else {
    emit('deleted', item.value)
    dialog.value = false
    setTimeout(() => deleting.value = false, 300)
  }
}

defineExpose({ open, create })
</script>

<template>
  <v-dialog
    v-model="dialog"
    :width="width"
  >
    <div
      v-if="item"
      class="dialog-wrapper"
    >
      <div class="dialog-header">
        <span v-if="isEditMode"> Редактирование платежа </span>
        <span v-else> Добавить платеж </span>
        <v-btn
          icon="$save"
          :size="48"
          variant="text"
          @click="save()"
        />
      </div>
      <div class="dialog-body">
        <div>
          <v-text-field
            ref="sumInput"
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
            v-model="item.method"
            label="Способ оплаты"
            :items="selectItems(ClientPaymentMethodLabel)"
          />
        </div>
        <div>
          <v-select
            v-model="item.company"
            label="Компания"
            :items="selectItems(CompanyTypeLabel)"
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
        <div
          v-if="isEditMode"
          class="dialog-bottom"
        >
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
