<script setup lang="ts">
import Codemirror from "codemirror-editor-vue3"
import type { EditorConfiguration } from "codemirror"
import "codemirror/mode/htmlmixed/htmlmixed.js"
import type { Macro, Macros } from "~/utils/models"
import { cloneDeep } from "lodash"

const cmOptions: EditorConfiguration = {
  tabSize: 4,
  mode: "text/html",
  lineNumbers: false,
  lineWrapping: true,
}
const code = ref("")
const dialog = ref(false)
const macros = ref<Macros>()
const macro = ref<Macro>()

onMounted(async () => {
  await loadData()
})

async function loadData() {
  const { data } = await useHttp<ApiResponse<Macros>>("macros")
  if (data.value) {
    const { data: newItems } = data.value
    macros.value = newItems
  }
}

function open(m: Macro) {
  macro.value = cloneDeep(m)
  dialog.value = true
}
</script>

<template>
  <div>
    <div class="table table--hover">
      <div v-for="m in macros" :key="m.id">
        <div>
          {{ m.title }}
        </div>
        <div class="table-actions">
          <v-btn variant="text" icon="$more" :size="48" @click="open(m)" />
        </div>
      </div>
    </div>
    <v-dialog fullscreen :width="1100" v-model="dialog">
      <div class="dialog-content" v-if="macro">
        <div class="dialog-header">
          {{ macro.title }}
          <v-btn
            icon="$save"
            :size="48"
            @click="dialog = false"
            color="#fafafa"
          />
        </div>
        <div class="dialog-body py-0">
          <Codemirror v-model:value="macro.text" :options="cmOptions" />
        </div>
      </div>
    </v-dialog>
  </div>
</template>

<style lang="scss">
.CodeMirror-code {
  pre {
    font-family: "ibm-plex" !important;
    font-size: 14px !important;
  }
}
</style>
