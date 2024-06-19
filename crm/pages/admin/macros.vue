<script setup lang="ts">
import Codemirror from 'codemirror-editor-vue3'
import type { EditorConfiguration } from 'codemirror'
import 'codemirror/mode/htmlmixed/htmlmixed.js'
import type { Macro, Macros } from '~/utils/models'

const cmOptions: EditorConfiguration = {
  tabSize: 4,
  mode: 'text/html',
  lineNumbers: false,
  lineWrapping: true,
}
const saving = ref(false)
const dialog = ref(false)
const macros = ref<Macros>()
const macro = ref<Macro>()

async function loadData() {
  const { data } = await useHttp<ApiResponse<Macros>>('macros')
  if (data.value) {
    const { data: newItems } = data.value
    macros.value = newItems
  }
}

function open(m: Macro) {
  macro.value = m
  dialog.value = true
}

async function save() {
  saving.value = true
  await useHttp(`macros/${macro.value?.id}`, {
    method: 'put',
    body: macro.value,
  })
  saving.value = false
  dialog.value = false
}

nextTick(loadData)
</script>

<template>
  <div>
    <div class="table table--hover table--actions-on-hover">
      <div
        v-for="m in macros"
        :key="m.id"
      >
        <div>
          {{ m.title }}
        </div>
        <div class="table-actions">
          <v-btn
            variant="plain"
            color="gray"
            icon="$edit"
            :size="48"
            @click="open(m)"
          />
        </div>
      </div>
    </div>
    <v-dialog
      v-model="dialog"
      fullscreen
      :width="1100"
    >
      <div
        v-if="macro"
        class="dialog-wrapper"
      >
        <div class="dialog-header">
          {{ macro.title }}
          <v-btn
            icon="$save"
            :size="48"
            color="#fafafa"
            :loading="saving"
            @click="save()"
          />
        </div>
        <div class="dialog-body py-0">
          <Codemirror
            v-model:value="macro.text"
            :options="cmOptions"
          />
        </div>
      </div>
    </v-dialog>
  </div>
</template>

<style lang="scss">
.CodeMirror-code {
  pre {
    font-family: 'ibm-plex' !important;
    font-size: 14px !important;
  }
}
</style>
