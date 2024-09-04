<script setup lang="ts">
import { clone } from 'rambda'

const emit = defineEmits<{
  updated: [r: RequestListResource]
  deleted: [r: RequestResource]
}>()

const modelDefaults: RequestResource = {
  status: 'new',
  program: null,
  responsible_user_id: null,
  comment: null,
  phones: [],
}

const { dialog, width } = useDialog('default')
const loading = ref(false)
const deleting = ref(false)
const itemId = ref<number>()
const request = ref<RequestResource>(modelDefaults)

function open(r: RequestResource) {
  request.value = clone(r)
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
  const { data } = await useHttp<RequestResource>(`requests/${r.id}`)
  if (data.value) {
    open(data.value)
  }
  loading.value = false
}

async function save() {
  dialog.value = false
  if (itemId.value) {
    const { data } = await useHttp<RequestListResource>(`requests/${itemId.value}`, {
      method: 'put',
      body: request.value,
    })
    if (data.value) {
      console.log('SAVED')
      emit('updated', data.value)
    }
  }
  else {
    const { data } = await useHttp<RequestListResource>('requests', {
      method: 'post',
      body: request.value,
    })
    if (data.value) {
      emit('updated', data.value)
    }
  }
  // emit('saved')
}

async function destroy() {
  if (!confirm('Вы уверены, что хотите удалить заявку?')) {
    return
  }
  deleting.value = true
  const { status } = await useHttp(`requests/${request.value.id}`, {
    method: 'delete',
  })
  if (status.value === 'error') {
    deleting.value = false
  }
  else {
    emit('deleted', request.value)
    dialog.value = false
    setTimeout(() => (deleting.value = false), 300)
  }
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
          <v-btn
            v-if="itemId"
            :loading="deleting"
            :size="48"
            class="remove-btn"
            icon="$delete"
            variant="text"
            @click="destroy()"
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
      <div
        v-else
        class="dialog-body"
      >
        <div>
          <v-select
            v-model="request.status"
            label="Статус"
            :items="selectItems(RequestStatusLabel)"
          />
        </div>
        <div>
          <UiClearableSelect
            v-model="request.program as string"
            label="Программа"
            :items="selectItems(ProgramLabel)"
          />
        </div>
        <div>
          <UserSelector
            v-model="request.responsible_user_id"
            label="Ответственный"
          />
        </div>
        <div>
          <v-textarea
            v-model="request.comment"
            label="Комментарий"
          />
        </div>
        <PhoneEditor v-model="request.phones" />
      </div>
    </div>
  </v-dialog>
</template>
