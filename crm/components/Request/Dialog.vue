<script setup lang="ts">
import type { RequestListResource, RequestResource } from '.'
import { cloneDeep } from 'lodash-es'
import { apiUrl, modelDefaults } from '.'

const emit = defineEmits<{
  updated: [r: RequestListResource]
  deleted: [r: RequestResource]
}>()

const { dialog, width } = useDialog('default')
const loading = ref(false)
const itemId = ref<number>()
const request = ref<RequestResource>(modelDefaults)

function open(r: RequestResource) {
  request.value = cloneDeep(r)
  dialog.value = true
}

function create() {
  itemId.value = undefined
  open(modelDefaults)
}

async function edit(r: RequestListResource) {
  itemId.value = r.id
  loading.value = true
  dialog.value = true
  const { data } = await useHttp<RequestResource>(`${apiUrl}/${r.id}`)
  if (data.value) {
    open(data.value)
  }
  loading.value = false
}

async function save() {
  dialog.value = false
  if (itemId.value) {
    const { data } = await useHttp<RequestListResource>(`${apiUrl}/${itemId.value}`, {
      method: 'put',
      body: request.value,
    })
    if (data.value) {
      console.log('SAVED')
      emit('updated', data.value)
    }
  }
  else {
    const { data } = await useHttp<RequestListResource>(apiUrl, {
      method: 'post',
      body: request.value,
    })
    if (data.value) {
      emit('updated', data.value)
    }
  }
  // emit('saved')
}

function onDeleted() {
  emit('deleted', request.value)
  dialog.value = false
}

defineExpose({ create, edit })
</script>

<template>
  <v-dialog
    v-model="dialog"
    :width="width"
  >
    <div
      v-if="request"
      class="dialog-wrapper"
    >
      <div class="dialog-header">
        <div v-if="itemId">
          Заявка {{ itemId }}
          <div class="dialog-subheader">
            <template v-if="request.created_at">
              {{ request.user ? formatName(request.user) : 'неизвестно' }}
              {{ formatDateTime(request.created_at) }}
            </template>
          </div>
        </div>
        <template v-else>
          Добавить заявку
        </template>
        <div>
          <CrudDeleteBtn
            :id="request.id"
            :api-url="apiUrl"
            confirm-text="Вы уверены, что хотите удалить заявку?"
            @deleted="onDeleted()"
          />
          <v-btn
            :size="48"
            icon="$save"
            variant="text"
            @click="save()"
          />
        </div>
      </div>
      <UiLoader v-if="loading" />
      <div v-else class="dialog-body">
        <div class="double-input">
          <v-select
            v-model="request.status"
            label="Статус"
            :items="selectItems(RequestStatusLabel)"
          />
          <UiClearableSelect
            v-model="request.direction"
            label="Направление"
            :items="selectItems(DirectionLabel)"
            nullify
          />
        </div>
        <div>
          <UserSelector
            v-model="request.responsible_user_id"
            label="Ответственный"
            nullify
          />
        </div>
        <div v-if="itemId">
          <ClientSelector v-model="request.client_id" :items="request.associated_clients" />
        </div>

        <PhoneEditor v-model="request.phones" />
        <template v-if="itemId">
          <div>
            <v-text-field
              disabled
              :model-value="request.yandex_id"
              label="Yandex ID"
            />
          </div>
          <div>
            <v-text-field
              disabled
              :model-value="request.google_id"
              label="Google ID"
            />
          </div>
          <div>
            <v-text-field
              disabled
              :model-value="request.ip"
              label="IP"
            />
          </div>
          <div>
            <v-text-field
              v-model="request.source"
              label="Источник"
            />
          </div>
        </template>
      </div>
    </div>
  </v-dialog>
</template>
