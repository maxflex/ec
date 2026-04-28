<script setup lang="ts">
import type { AiInstructionItem } from '.'

const { dialog, width } = useDialog(900)
const title = ref('Просмотр инструкции')
const item = ref<AiInstructionItem>()

const parts = computed(() => {
  // Единый формат хранения: instruction + разделитель + prompt.
  const [instructionRaw = '', promptRaw = ''] = (item.value?.text || '').split('<USER_PROMPT>')

  return {
    instruction: decodeHtmlEntities(instructionRaw.trim()),
    prompt: decodeHtmlEntities(promptRaw.trim()),
  }
})

function decodeHtmlEntities(value: string): string {
  return value
    .replace(/&quot;/g, '"')
    .replace(/&#34;/g, '"')
    .replace(/&apos;/g, '\'')
    .replace(/&#39;/g, '\'')
    .replace(/&amp;/g, '&')
    .replace(/&lt;/g, '<')
    .replace(/&gt;/g, '>')
    .replace(/&nbsp;/g, ' ')
    .replace(/&#(\d+);/g, (_, dec: string) => String.fromCodePoint(Number(dec)))
    .replace(/&#x([0-9a-fA-F]+);/g, (_, hex: string) => String.fromCodePoint(Number.parseInt(hex, 16)))
}

function open(dialogTitle: string, instruction: AiInstructionItem): void {
  if (!instruction.text) {
    return
  }

  title.value = dialogTitle
  item.value = instruction
  dialog.value = true
}

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div v-if="item" class="dialog-wrapper">
      <div class="dialog-header">
        <div>
          {{ title }}
          <div v-if="item.created_at" class="dialog-subheader d-flex align-center ga-4">
            <div>
              {{ formatDateTime(item.created_at) }}
            </div>
            <div class="vfn-1">
              {{ item.model }}
            </div>
          </div>
        </div>
        <v-btn icon="$close" :size="48" variant="text" @click="dialog = false" />
      </div>
      <div class="dialog-body">
        <h2>Инструкция</h2>
        <div class="ai-instruction-dialog__text">
          {{ parts.instruction }}
        </div>

        <h2 class="mt-6">
          Промпт
        </h2>
        <div class="ai-instruction-dialog__text">
          {{ parts.prompt }}
        </div>
      </div>
    </div>
  </v-dialog>
</template>

<style scoped lang="scss">
.ai-instruction-dialog {
  &__text {
    white-space: pre-wrap;
    word-break: break-word;
    font-family: 'ibm-plex', monospace;
    font-size: 14px;
    line-height: 21px;
  }
}
</style>
