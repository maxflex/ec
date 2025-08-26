<script setup lang="ts">
import type { EditorConfiguration } from 'codemirror'
import type { PrintOption } from '.'
import Codemirror from 'codemirror-editor-vue3'
import 'codemirror/mode/htmlmixed/htmlmixed.js'

const { dialog, width, transition } = useDialog('large')
const text = ref<string>()
const cmOptions: EditorConfiguration = {
  tabSize: 4,
  mode: 'text/html',
  lineNumbers: false,
  lineWrapping: true,
}

async function open(p: PrintOption, params: object = {}, skipPreview: boolean = false) {
  text.value = undefined
  const { data } = await useHttp<{ text: string }>(
    `print`,
    {
      method: 'post',
      body: {
        id: p.id,
        company: p.company,
        ...params,
      },
    },
  )
  text.value = data.value!.text
  if (skipPreview) {
    print()
    return
  }
  dialog.value = true
}

function print() {
  dialog.value = false
  setTimeout(() => {
    // Create an invisible iframe
    const iframe = document.createElement('iframe')
    iframe.style.position = 'absolute'
    iframe.style.width = '0'
    iframe.style.height = '0'
    iframe.style.border = 'none'

    document.body.appendChild(iframe)

    // Get the iframe's document for injecting HTML content
    const iframeDocument = iframe.contentWindow?.document

    if (iframeDocument) {
      iframeDocument.open()
      iframeDocument.write(`
        <html lang="ru">
          <head>
            <title>ЕГЭ-Центр</title>
            <style>
              /* Add custom styles here if necessary */
              body { font-family: Arial, sans-serif; margin: 20px; }
              .print__contract h4 {text-align: center}
            </style>
          </head>
          <body>
            ${text.value}
          </body>
        </html>
      `)
      iframeDocument.close()

      // Wait for the content to load before printing
      iframe.onload = function () {
        iframe.contentWindow?.focus()
        iframe.contentWindow?.print()

        // Clean up by removing the iframe after printing
        document.body.removeChild(iframe)
      }
    }
    else {
      console.error('Failed to access iframe document.')
    }
  }, 300)
}

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width" :transition="transition">
    <div class="dialog-wrapper">
      <div class="dialog-header">
        Предварительный просмотр
        <v-btn
          icon="$print"
          :size="48"
          variant="text"
          @click="print()"
        />
      </div>
      <div class="dialog-body pt-0">
        <UiLoader v-if="text === undefined" />
        <Codemirror
          v-else
          v-model:value="text"
          :options="cmOptions"
        />
      </div>
    </div>
  </v-dialog>
</template>
