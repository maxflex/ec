<script setup lang="ts">
import type { SavedProjectResource } from '.'
import { clone, cloneDeep } from 'lodash-es'
import { VDialogTransition } from 'vuetify/components'
import { apiUrl } from '.'

const emit = defineEmits(['saved'])
const dialog = ref(false)
const saving = ref(false)
const textInput = ref()
const router = useRouter()
const body = reactive<{
  contract_id: number
  comment: string
}>({
  contract_id: -1,
  comment: '',
})

function open(contractId: number) {
  body.contract_id = contractId
  body.comment = ''
  dialog.value = true
  setTimeout(() => textInput.value.focus(), 50)
}

async function save() {
  saving.value = true
  const { data } = await useHttp<SavedProjectResource>(
    `${apiUrl}/save`,
    {
      method: 'POST',
      body: cloneDeep(body),
    },
  )
  const id = data.value!.id
  const link = router.resolve({ name: 'projects-editor', query: { id } }).href
  useGlobalMessage(`<a href="${link}">Проект №${id}</a> сохранён`, 'success')
  emit('saved')
  dialog.value = false
  setTimeout(() => (saving.value = false), 300)
}

defineExpose({ open })
</script>

<template>
  <v-dialog
    v-model="dialog"
    max-width="500"
    location="center"
    :content-class="null"
    :transition="{ component: VDialogTransition }"
  >
    <v-card>
      <v-card-text>
        <div class="font-weight-bold mb-6">
          <span v-if="body.contract_id > 0" class="text-gray">
            Договору №{{ body.contract_id }} /
          </span>
          <span v-else class="text-gray">
            Новый договор /
          </span>
          Сохранение проекта
        </div>
        <v-text-field ref="textInput" v-model="body.comment" class="mt-1" label="Комментарий к проекту" @keydown.enter="save()" />
      </v-card-text>
      <v-card-actions class="pb-4">
        <v-spacer />
        <v-btn
          color="primary" variant="flat"
          :width="160"
          :loading="saving"
          @click="save()"
        >
          Сохранить
        </v-btn>
        <v-spacer />
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>
