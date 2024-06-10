<script setup lang="ts">
import { clone } from 'rambda'

const modelDefaults: UserResource = {
  id: newId(),
  first_name: null,
  last_name: null,
  middle_name: null,
  phones: [],
}
const { dialog, width } = useDialog('default')
const loading = ref(false)
const itemId = ref<number | undefined>()
const item = ref<UserResource>(clone(modelDefaults))
const destroying = ref(false)

const emit = defineEmits<{ (e: 'updated' | 'destroyed', u: UserResource): void }>()

function openDialog(u: UserResource) {
  item.value = clone(u)
  dialog.value = true
}

function create() {
  itemId.value = undefined
  openDialog(modelDefaults)
}

async function edit(u: UserResource) {
  itemId.value = u.id
  loading.value = true
  const { data } = await useHttp<UserResource>(`users/${u.id}`)
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
  destroying.value = true
  const { data, status } = await useHttp<UserResource>(`users/${item.value.id}`, {
    method: 'delete',
  })
  if (status.value === 'error') {
    destroying.value = false
  }
  else if (data.value) {
    emit('destroyed', data.value)
    dialog.value = false
    setTimeout(() => destroying.value = false, 300)
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
        <template v-if="itemId">
          Редактирование пользователя
        </template>
        <template v-else>
          Новый пользователь
        </template>
        <v-btn
          icon="$save"
          :size="48"
          color="#fafafa"
          @click="save()"
        />
      </div>
      <UiLoaderr v-if="loading" />
      <div
        v-else
        class="dialog-body"
      >
        <div class="text-center">
          <UserAvatar
            :user="item"
            :size="150"
          />
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
        <div>
          <v-text-field
            v-model="item.middle_name"
            label="Отчество"
          />
        </div>
        <UiPhoneEditor v-model="item.phones" />
        <div
          v-if="itemId"
          class="dialog-bottom"
        >
          <span v-if="item.created_at">
            пользователь создан
            {{ formatDateTime(item.created_at) }}
          </span>
          <v-btn
            icon="$delete"
            :size="48"
            color="red"
            variant="plain"
            :loading="destroying"
            @click="destroy()"
          />
        </div>
      </div>
    </div>
  </v-dialog>
</template>
