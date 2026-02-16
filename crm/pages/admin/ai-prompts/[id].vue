<script setup lang="ts">
import type { EditorConfiguration } from 'codemirror'
import Codemirror from 'codemirror-editor-vue3'
import 'codemirror/mode/htmlmixed/htmlmixed.js'

const route = useRoute()
const saving = ref(false)
const item = ref<AiPromptResource>()
const cmOptions: EditorConfiguration = {
  tabSize: 4,
  mode: 'text/html',
  lineNumbers: false,
  lineWrapping: true,
}

async function loadData() {
  const { data } = await useHttp<AiPromptResource>(`ai-prompts/${route.params.id}`)
  if (data.value) {
    item.value = data.value
  }
}

async function save() {
  if (!item.value) {
    return
  }

  // Сохраняем только редактируемые поля prompt-шаблона.
  saving.value = true
  await useHttp(
    `ai-prompts/${item.value.id}`,
    {
      method: 'put',
      body: {
        id: item.value.id,
        title: item.value.title,
        text: item.value.text,
      },
    },
  )

  setTimeout(() => saving.value = false, 300)
  useGlobalMessage('Сохранено', 'success')
}

nextTick(loadData)
</script>

<template>
  <UiLoader v-if="item === undefined" />
  <template v-else>
    <UiFilters>
      <v-text-field v-model="item.title" label="Заголовок" density="comfortable" />
      <template #buttons>
        <v-btn color="primary" :loading="saving" @click="save()">
          сохранить
        </v-btn>
      </template>
    </UiFilters>

    <div class="px-5">
      <Codemirror
        v-model:value="item.text"
        :options="cmOptions"
      />
    </div>
  </template>
</template>
