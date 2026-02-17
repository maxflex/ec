<script setup lang="ts">
import type { EditorConfiguration } from 'codemirror'
import { defineAsyncComponent } from 'vue'

// Редактор загружаем только на клиенте, иначе SSR падает на CSS-импорте codemirror.
const Codemirror = defineAsyncComponent(() => import('codemirror-editor-vue3'))
if (import.meta.client) {
  void import('codemirror/mode/htmlmixed/htmlmixed.js')
}

const route = useRoute()
const saving = ref(false)
const field = ref<'instruction' | 'prompt'>('instruction')
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
    item.value = {
      ...data.value,
      instruction: data.value.instruction ?? '',
      prompt: data.value.prompt ?? '',
    }
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
        instruction: item.value.instruction,
        prompt: item.value.prompt,
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
      <v-select
        v-model="field"
        :items="[
          { title: 'Инструкция', value: 'instruction' },
          { title: 'Промпт', value: 'prompt' },
        ]"
        label="Поле"
        density="comfortable"
      />
      <template #buttons>
        <v-btn color="primary" :loading="saving" @click="save()">
          сохранить
        </v-btn>
      </template>
    </UiFilters>
    <div class="px-5">
      <Codemirror
        v-if="field === 'instruction'"
        v-model:value="item.instruction"
        :options="cmOptions"
      />
      <Codemirror
        v-else
        v-model:value="item.prompt"
        :options="cmOptions"
      />
    </div>
  </template>
</template>
