<script setup lang="ts">
import { cloneDeep } from 'lodash-es'
import type { Request, RequestStatus, Program } from '~/utils/models'
import { REQUEST_STATUS, PROGRAM } from '~/utils/sment'

const { dialog, width } = useDialog()
const request = ref<Request>()

function open(r: Request) {
  request.value = cloneDeep(r)
  dialog.value = true
}

function save() {
  dialog.value = false
  emit('saved')
}

defineExpose({ open })
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
            :items="
              Object.keys(REQUEST_STATUS).map((value) => ({
                value,
                title: REQUEST_STATUS[value as RequestStatus],
              }))
            "
          />
        </div>
        <div>
          <v-select
            v-model="request.program"
            label="Программа"
            :items="
              Object.keys(PROGRAM).map((value) => ({
                value,
                title: PROGRAM[value as Program],
              }))
            "
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
