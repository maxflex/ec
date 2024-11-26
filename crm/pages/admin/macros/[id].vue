<script setup lang="ts">
import type { EditorConfiguration } from 'codemirror'
import Codemirror from 'codemirror-editor-vue3'
import 'codemirror/mode/htmlmixed/htmlmixed.js'

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
  <template v-else>
    <div class="macro__title">
      <div>
        <v-text-field v-model="item.title" label="Заголовок" />
      </div>
      <div>
        <v-select v-model="company" :items="selectItems(CompanyLabel)" label="Компания" />
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
    <div class="px-5">
      <Codemirror
        v-if="company === 'ooo'"
        v-model:value="item.text_ooo"
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

<style lang="scss">
.CodeMirror {
  &-code {
    pre {
      font-family: 'ibm-plex', serif !important;
      font-size: 14px !important;
    }
  }
  &-gutters {
    display: none !important;
  }
  &-sizer {
    margin: 0 !important;
  }
}

.macro {
  &__title {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    padding: 20px;
    padding-bottom: 5px;
    position: sticky;
    top: 0;
    z-index: 1;
    background: white;
    box-shadow: 0 0 10px 10px white;
    margin-bottom: 20px;
    & > div {
      flex: 1;
    }
  }
}
</style>
