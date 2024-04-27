<script lang="ts" setup>
import type { RequestStatus } 
from "~/utils/models"
import { REQUEST_STATUS } from "~/utils/sment"

const { dialog, width } = useDialog()

const filters = ref<{ status?: RequestStatus }>({})

const statuses = Object.keys(REQUEST_STATUS).map((value) => ({
  value,
  title: REQUEST_STATUS[value as RequestStatus],
}))

// Object.keys(REQUEST_STATUS)
//   .map((value) => ({
//     value,
//     title: REQUEST_STATUS[value as RequestStatus],
//   }))
//   .concat([
//     {
//       value: null,
//       title: "не установлено",
//     },
//   ])

function open() {
  dialog.value = true
}

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-wrapper">
      <div class="dialog-header">Фильтры</div>
      <div class="dialog-body">
        <div>
          <v-select label="Статус" v-model="filters.status" :items="statuses">
          </v-select>
        </div>
        <div>
          {{ filters }}
        </div>
      </div>
    </div>
  </v-dialog>
</template>
