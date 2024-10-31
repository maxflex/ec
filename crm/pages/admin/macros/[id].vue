<script setup lang="ts">
import type { EditorConfiguration } from 'codemirror'
import Codemirror from 'codemirror-editor-vue3'
import 'codemirror/mode/htmlmixed/htmlmixed.js'

const route = useRoute()
const saving = ref(false)
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
    console.log(data.value)
  }
}

async function save() {
  saving.value = true
  await useHttp(`macros/${item.value?.id}`, {
    method: 'put',
    body: item.value,
  })
  setTimeout(() => saving.value = false, 300)
}

nextTick(loadData)
</script>

<template>
  <UiLoader v-if="item === undefined" />
  <div v-else class="macro">
    <div class="macro__title">
      <div>
        <v-text-field v-model="item.title" label="Заголовок" />
      </div>
      <div class="text-right">
        <v-btn
          icon="$save"
          :size="48"
          variant="text"
          :loading="saving"
          @click="save()"
        />
      </div>
    </div>
    <div>
      <Codemirror
        v-model:value="item.text"
        :options="cmOptions"
      />
    </div>
    <div class="macro__actions" />
  </div>
</template>

<style lang="scss">
.CodeMirror-code {
  pre {
    font-family: 'ibm-plex' !important;
    font-size: 14px !important;
  }
}
.macro {
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 30px;
  &__title {
    display: flex;
    align-items: center;
    justify-content: space-between;
    & > div {
      flex: 1;
    }
  }
}
</style>
