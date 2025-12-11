<script setup lang="ts">
import type { ContractResource } from '../ContractVersion'
import { cloneDeep } from 'lodash-es'

const { dialog, width } = useDialog('default')

const contract = ref<ContractResource>()
const saving = ref(false)

function open(c: ContractResource) {
  contract.value = cloneDeep(c)
  dialog.value = true
}

function save() {
  saving.value = true
  const { id } = contract.value!
  useHttp(`contracts/${id}`, {
    method: 'PUT',
    body: cloneDeep(contract.value),
  })
  dialog.value = false
  saving.value = false
  useGlobalMessage(`Договор №${id} сохранён`, 'success')
}

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div v-if="contract" class="dialog-wrapper contract-version-dialog">
      <div class="dialog-header">
        <div>
          Редактирование договора
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
          <v-select
            v-model="contract.company"
            label="Компания"
            :items="selectItems(CompanyLabel)"
          />
        </div>
        <div>
          <v-textarea
            v-model="contract.source"
            label="Источник"
            no-resize
            rows="5"
          />
        </div>
        <div>
          <v-checkbox
            v-model="contract.is_realized"
            label="Реализован"
          />
        </div>
      </div>
    </div>
  </v-dialog>
</template>
