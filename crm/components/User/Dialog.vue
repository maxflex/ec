<script setup lang="ts">
import { cloneDeep } from 'lodash-es'

const emit = defineEmits<{ (e: 'updated' | 'destroyed', u: UserResource): void }>()
const modelDefaults: UserResource = {
  id: newId(),
  first_name: null,
  last_name: null,
  middle_name: null,
  entity_type: EntityTypeValue.user,
  is_active: false,
  is_call_notifications: false,
  photo_url: null,
  phones: [],
}
const { dialog, width } = useDialog('default')
const loading = ref(false)
const itemId = ref<number | undefined>()
const item = ref<UserResource>(cloneDeep(modelDefaults))
const deleting = ref(false)

function openDialog(u: UserResource) {
  item.value = cloneDeep(u)
  dialog.value = true
}

function create() {
  itemId.value = undefined
  openDialog(modelDefaults)
}

async function edit(userId: number) {
  itemId.value = userId
  loading.value = true
  const { data } = await useHttp<UserResource>(`users/${userId}`)
  if (data.value) {
    openDialog(data.value)
  }
  loading.value = false
}

async function save() {
  dialog.value = false
  loading.value = true
  const method = itemId.value ? 'put' : 'post'
  const url = itemId.value ? `users/${itemId.value}` : 'users'
  const { data } = await useHttp<UserResource>(url, {
    method,
    body: item.value,
  })
  if (data.value) {
    emit('updated', data.value)
  }
  loading.value = false
}

async function destroy() {
  if (!confirm('Вы уверены, что хотите удалить пользователя?')) {
    return
  }
  deleting.value = true
  const { data, status } = await useHttp<UserResource>(`users/${item.value.id}`, {
    method: 'delete',
  })
  if (status.value === 'error') {
    deleting.value = false
  }
  else if (data.value) {
    emit('destroyed', data.value)
    dialog.value = false
    setTimeout(() => deleting.value = false, 300)
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
      v-if="item"
      class="dialog-wrapper"
    >
      <div class="dialog-header">
        <div v-if="itemId">
          Редактирование пользователя
          <div class="dialog-subheader">
            <template v-if="item.created_at">
              {{ formatDateTime(item.created_at) }}
            </template>
          </div>
        </div>
        <template v-else>
          Новый пользователь
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
        <div class="text-center">
          <AvatarLoader :item="item" />
        </div>
        <div>
          <v-text-field
            v-model="item.last_name"
            label="Фамилия"
          />
        </div>
        <div>
          <v-text-field
            v-model="item.first_name"
            label="Имя"
          />
        </div>
        <PhoneEditor v-model="item.phones" :disabled="!!itemId" />
        <div>
          <v-checkbox
            v-model="item.is_active"
            label="Действующий сотрудник"
          />
          <v-checkbox
            v-model="item.is_call_notifications"
            label="Уведомления о звонках"
          />
        </div>
      </div>
    </div>
  </v-dialog>
</template>
