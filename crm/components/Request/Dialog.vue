<script setup lang="ts">
import type { Request, RequestStatus, Program } from "~/utils/models"
import { cloneDeep } from "lodash"
import { REQUEST_STATUS, PROGRAM } from "~/utils/sment"

const { dialog, width } = useDialog()
const request = ref<Request>()

function open(r: Request) {
  request.value = cloneDeep(r)
  dialog.value = true
}

function save() {
  dialog.value = false
  emit("saved")
}

defineExpose({ open })
const emit = defineEmits<{ (e: "saved"): void }>()
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-wrapper" v-if="request">
      <div class="dialog-header">
        <template v-if="request.id"> Заявка {{ request.id }} </template>
        <template v-else>Добавить заявку</template>
        <v-btn icon="$save" :size="48" @click="save()" color="#fafafa" />
      </div>
      <div class="dialog-body">
        <div>
          <v-select
            label="Статус"
            v-model="request.status"
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
            label="Программа"
            v-model="request.program"
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
            label="Ответственный"
            v-model="request.responsible_user_id"
          />
        </div>
        <div>
          <v-textarea label="Комментарий" v-model="request.comment" />
        </div>
      </div>
    </div>
  </v-dialog>
</template>
