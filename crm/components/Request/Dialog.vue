<script setup lang="ts">
import { clone } from 'rambda'

const modelDefaults: RequestResource = {
  status: 'new',
  program: null,
  responsible_user_id: null,
  comment: null,
}

const { dialog, width } = useDialog()
const request = ref<RequestResource>(modelDefaults)

function open(r: RequestResource) {
  request.value = clone(r)
  dialog.value = true
}

function create() {
  open(modelDefaults)
}

function save() {
  dialog.value = false
  emit('saved')
}

defineExpose({ open, create })
const emit = defineEmits<{ (e: 'saved'): void }>()
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
        <template v-if="request.id">
          Заявка {{ request.id }}
        </template>
        <template v-else>
          Добавить заявку
        </template>
        <v-btn
          icon="$save"
          :size="48"
          color="#fafafa"
          @click="save()"
        />
      </div>
      <div class="dialog-body">
        <div>
          <v-select
            v-model="request.status"
            label="Статус"
            :items="selectItems(RequestStatusLabel)"
          />
        </div>
        <div>
          <UiClearableSelect
            v-model="request.program"
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
      </div>
    </div>
  </v-dialog>
</template>
