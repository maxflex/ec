<script setup lang="ts">
import { cloneDeep } from 'lodash-es'

const { width, dialog } = useDialog('default')
const loading = ref(false)
const saving = ref(false)
const item = ref<ClientLessonResource>()
const priceMask = { mask: '####' }

async function edit(id: number) {
  dialog.value = true
  loading.value = true
  const { data } = await useHttp<ClientLessonResource>(`client-lessons/${id}`)
  if (data.value) {
    item.value = data.value
  }
  loading.value = false
}

/**
 * Сохранить ранее проведённое занятие
 */
async function save() {
  if (!item.value) {
    return
  }
  saving.value = true
  const { error } = await useHttp<ClientLessonResource>(
    `client-lessons/${item.value.id}`,
    {
      method: 'put',
      body: cloneDeep(item.value),
    },
  )
  if (error.value) {
    // errors.value = error.value.data.errors
    loading.value = false
    return
  }
  dialog.value = false
  setTimeout(() => saving.value = false, 300)
}

defineExpose({ edit })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-wrapper">
      <div class="dialog-header">
        <div>
          Редактировать цену
        </div>
        <div>
          <v-btn
            icon="$save"
            :size="48"
            variant="text"
            :loading="saving"
            @click="save()"
          />
        </div>
      </div>
      <UiLoader v-if="loading" />
      <div v-else-if="item" class="dialog-body">
        <div>
          <v-text-field
            v-model="item.price"
            v-maska="priceMask"
            label="Цена, руб."
          />
        </div>
        <div>
          <ContractSelector v-model="item.contract_version_program_id" />
        </div>
      </div>
    </div>
  </v-dialog>
</template>
