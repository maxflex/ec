<script setup lang="ts">
import { clone } from 'rambda'

const emit = defineEmits<{
  updated: [pass: PassResource]
  created: [pass: PassResource]
  deleted: [pass: PassResource]
}>()
const { dialog, width } = useDialog('default')
const modelDefaults: PassResource = {
  id: newId(),
  type: 'person',
  date: '',
  comment: '',
  request_id: null,
  used_at: null,
  is_expired: false,
}

const saving = ref(false)
const deleting = ref(false)
const item = ref<PassResource>(modelDefaults)
const isDisabled = computed(() => item.value.is_expired || !!item.value.used_at)

function create(requestId: number | null = null) {
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
        `passes/${item.value.id}`,
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
    const { data } = await useHttp<PassResource>(
        `passes`,
        {
          method: 'post',
          body: item.value,
        },
    )
    if (data.value) {
      emit('created', data.value)
    }
  }
  dialog.value = false
  saving.value = false
}

async function destroy() {
  if (!confirm('Вы уверены, что хотите удалить пропуск?')) {
    return
  }
  deleting.value = true
  await useHttp(`passes/${item.value.id}`, {
    method: 'delete',
  })
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
          <v-btn
            v-if="item.id > 0"
            :loading="deleting"
            :size="48"
            class="remove-btn"
            icon="$delete"
            variant="text"
            @click="destroy()"
          />
          <v-btn icon="$save" variant="text" :size="48" :loading="saving" @click="save()" />
        </div>
      </div>
      <div class="dialog-body">
        <div>
          <v-select
            v-model="item.type"
            label="Тип"
            :items="selectItems(PassTypeLabel)"
            :disabled="isDisabled"
          />
        </div>
        <div>
          <UiDateInput v-model="item.date" label="Дата" :disabled="isDisabled" />
        </div>
        <div>
          <v-text-field v-model="item.comment" label="ФИО" :disabled="isDisabled" />
        </div>
      </div>
    </div>
  </v-dialog>
</template>
