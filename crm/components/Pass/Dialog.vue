<script setup lang="ts">
import { clone } from 'rambda'
import { apiUrl, modelDefaults, type PassResource } from '.'

const emit = defineEmits<{
  updated: [pass: PassResource]
  created: [pass: PassResource]
  deleted: [pass: PassResource]
}>()
const { dialog, width } = useDialog('default')
const saving = ref(false)
const item = ref<PassResource>(modelDefaults)
const isDisabled = computed(() => item.value.is_expired || !!item.value.used_at)

function create(requestId: number | undefined = undefined) {
  dialog.value = true
  item.value = {
    ...modelDefaults,
    request_id: requestId,
  }
}

function edit(pass: PassResource) {
  dialog.value = true
  item.value = clone(pass)
}

async function save() {
  saving.value = true
  if (item.value.id > 0) {
    const { data } = await useHttp<PassResource>(
      `${apiUrl}/${item.value.id}`,
      {
        method: 'put',
        body: item.value,
      },
    )
    if (data.value) {
      emit('updated', data.value)
    }
  }
  else {
    const { data } = await useHttp<PassResource>(apiUrl, {
      method: 'post',
      body: item.value,
    })
    if (data.value) {
      emit('created', data.value)
    }
  }
  dialog.value = false
  saving.value = false
}

function onDeleted() {
  emit('deleted', item.value)
  dialog.value = false
}

defineExpose({ create, edit })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-wrapper">
      <div class="dialog-header">
        <div v-if="item.id > 0">
          Редактировать пропуск
          <div class="dialog-subheader">
            {{ formatName(item.user!) }}
            {{ formatDateTime(item.created_at!) }}
          </div>
        </div>
        <template v-else>
          Добавить пропуск
          <template v-if="item.request_id">
            к заявке {{ item.request_id }}
          </template>
        </template>
        <div v-if="!isDisabled">
          <DialogDeleteBtn
            :id="item.id"
            :api-url="apiUrl"
            confirm-text="Вы уверены, что хотите удалить пропуск?"
            @deleted="onDeleted()"
          />
          <v-btn icon="$save" variant="text" :size="48" :loading="saving" @click="save()" />
        </div>
      </div>
      <div class="dialog-body">
        <div>
          <UiDateInput v-model="item.date" label="Дата" :disabled="isDisabled" />
        </div>
        <div>
          <v-text-field
            v-model="item.comment"
            label="ФИО"
            :disabled="isDisabled"
          />
        </div>
      </div>
    </div>
  </v-dialog>
</template>
