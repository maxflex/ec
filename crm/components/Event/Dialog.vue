<script setup lang="ts">
import { clone } from 'rambda'

const { width, dialog } = useDialog('default')
const timeMask = { mask: '##:##' }
const saving = ref(false)
const itemId = ref<number>()
const modelDefaults: EventResource = {
  id: newId(),
  name: '',
  date: today(),
  description: null,
  duration: null,
  is_afterclass: false,
}
const item = ref<EventResource>(modelDefaults)

function create() {
  itemId.value = undefined
  item.value = clone(modelDefaults)
  dialog.value = true
}

async function save() {
  saving.value = true
  if (itemId.value) {
    //
  }
  else {
    const { data } = await useHttp(`events`, {
      method: 'post',
      body: item.value,
    })
    console.log(data)
  }
  saving.value = false
}

defineExpose({ create })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-wrapper">
      <div class="dialog-header">
        <template v-if="itemId">
          Редактировать событие
        </template>
        <template v-else>
          Новое событие
        </template>
        <v-btn
          icon="$save"
          :size="48"
          color="#fafafa"
          @click="save()"
        />
      </div>
      <div class="dialog-body">
        <div class="double-input">
          <UiDateInput v-model="item.date" />
          <div>
            <v-text-field
              v-model="item.time"
              v-maska:[timeMask]
              label="Время"
            />
          </div>
        </div>
        <div>
          <v-text-field
            v-model="item.duration"
            label="Длительность"
            type="number"
            suffix="минут"
            hide-spin-buttons
          />
        </div>
        <div>
          <v-text-field v-model="item.name" label="Название" />
        </div>
        <div>
          <v-textarea v-model="item.description" label="Описание" />
        </div>
        <div>
          <v-checkbox
            v-model="item.is_afterclass"
            label="Внеучебное"
          />
        </div>
      </div>
    </div>
  </v-dialog>
</template>
