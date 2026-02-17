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
const company = ref<Company>('ooo')
const item = ref<MacroResource>()
const cmOptions: EditorConfiguration = {
  tabSize: 4,
  mode: 'text/html',
  lineNumbers: false,
  lineWrapping: true,
}

async function loadData() {
  const { data } = await useHttp<MacroResource>(
    `macros/${route.params.id}`,
  )
  if (data.value) {
    item.value = data.value
  }
}

async function save() {
  if (!item.value) {
    return
  }
  const field = `text_${company.value}` as keyof MacroResource
  saving.value = true
  await useHttp(
    `macros/${item.value?.id}`,
    {
      method: 'put',
      body: {
        id: item.value.id,
        [field]: item.value[field],
      },
    },
  )
  setTimeout(() => saving.value = false, 300)
  useGlobalMessage(`Макрос ${CompanyLabel[company.value]} сохранён`, 'success')
}

nextTick(loadData)
</script>

<template>
  <UiLoader v-if="item === undefined" />
  <template v-else>
    <UiFilters>
      <v-text-field v-model="item.title" label="Заголовок" density="comfortable" />
      <v-select v-model="company" :items="selectItems(CompanyLabel)" label="Компания" density="comfortable" />
      <template #buttons>
        <v-btn color="primary" :loading="saving" @click="save()">
          сохранить
        </v-btn>
      </template>
    </UiFilters>
    <div class="px-5">
      <Codemirror
        v-if="company === 'ooo'"
        v-model:value="item.text_ooo"
        :options="cmOptions"
      />
      <Codemirror
        v-else-if="company === 'ano'"
        v-model:value="item.text_ano"
        :options="cmOptions"
      />
      <Codemirror
        v-else
        v-model:value="item.text_ip"
        :options="cmOptions"
      />
    </div>
  </template>
</template>
