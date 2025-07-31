<script setup lang="ts">
import type { ContractResource } from '../ContractVersion'

const { dialog, width } = useDialog('default')

const contract = ref<ContractResource>()
const saving = ref(false)

function open(c: ContractResource) {
  contract.value = c
  dialog.value = true
}

function save() {
  saving.value = true
  const { id, source } = contract.value!
  useHttp(`contracts/${id}`, {
    method: 'PUT',
    body: {
      source,
    },
  })
  dialog.value = false
  saving.value = false
  useGlobalMessage(`Источник сохранён для договора №${id}`, 'success')
}

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div v-if="contract" class="dialog-wrapper contract-version-dialog">
      <div class="dialog-header">
        <div>
          Редактирование источника
          <div class="dialog-subheader">
            Договор №{{ contract.id }}
          </div>
        </div>
        <v-btn
          icon="$save"
          :size="48"
          :loading="saving"
          variant="text"
          @click="save()"
        />
      </div>
      <div class="dialog-body">
        <div>
          <v-textarea
            v-model="contract.source"
            label="Источник"
            no-resize
            rows="5"
          />
        </div>
      </div>
    </div>
  </v-dialog>
</template>
