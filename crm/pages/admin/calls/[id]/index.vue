<script setup lang="ts">
import type { CallerType, CallListResource } from '~/components/Call'
import { mdiDotsHorizontal } from '@mdi/js'
import { CallerTypeLabel } from '~/components/Call'

type CallInstructionType = 'transcription' | 'analysis'

interface CallInstructionItem {
  text: string | null
  created_at: string | null
}

interface CallInstructionFields {
  transcription: CallInstructionItem
  analysis: CallInstructionItem
}

interface CallResource extends CallListResource {
  analysis_1: string | null
  analysis_2: string | null
  instruction: CallInstructionFields | null
}

interface CallTranscribeFields {
  transcript: string
  instruction: CallInstructionFields
}

interface CallAnalyzeFields {
  summary: string
  analysis_1: string | null
  analysis_2: string | null
  caller_type: CallerType
  instruction: CallInstructionFields
}

const downloading = ref(false)
const transcribing = ref(false)
const analyzing = ref(false)
const isInstructionDialogOpen = ref(false)
const selectedInstructionType = ref<CallInstructionType>('transcription')
const route = useRoute()
const item = ref<CallResource>()

const { tabs, selectedTab, tabCounts } = useTabs({
  transcript: 'транскрипт',
  summary: 'краткое содержание',
  analysis_1: 'анализ',
  analysis_2: 'анализ 2',
})

const selectedInstructionItem = computed<CallInstructionItem>(() => {
  return item.value?.instruction?.[selectedInstructionType.value] ?? { text: null, created_at: null }
})

const selectedInstructionCreatedAt = computed<string | null>(() => {
  return selectedInstructionItem.value.created_at
})

const selectedInstructionParts = computed(() => {
  const [instructionRaw = '', promptRaw = ''] = (selectedInstructionItem.value.text || '').split('<USER_PROMPT>')

  return {
    instruction: decodeHtmlEntities(instructionRaw.trim()),
    prompt: decodeHtmlEntities(promptRaw.trim()),
  }
})

async function loadData() {
  const { data } = await useHttp<CallResource>(`calls/${route.params.id}`)
  item.value = data.value as CallResource
}

async function downloadRecording() {
  if (!item.value?.recording_url) {
    return
  }

  downloading.value = true
  try {
    // Скачиваем через Blob, чтобы браузер показал именно скачивание,
    // а не встроенный плеер в новой вкладке.
    const response = await fetch(item.value.recording_url)
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`)
    }
    const audioBlob = await response.blob()
    const audio = URL.createObjectURL(audioBlob)
    const link = document.createElement('a')
    link.href = audio
    link.download = `${item.value.entry_id}.mp3`
    link.click()
    setTimeout(() => URL.revokeObjectURL(audio), 1000)
  }
  catch (error) {
    console.error('Call details: recording download failed', error)
    useGlobalMessage('Не удалось скачать аудиозапись', 'error')
  }
  finally {
    setTimeout(() => (downloading.value = false), 300)
  }
}

async function transcribe() {
  transcribing.value = true
  const { data, error } = await useHttp<CallTranscribeFields>(
    `calls/${item.value!.id}/transcribe`,
    {
      method: 'POST',
    },
  )
  if (error.value) {
    setTimeout(() => useGlobalMessage(`<b>Ошибка ИИ (транскрипт)</b>: ${error.value!.data.message}`, 'error'), 100)
  }
  else if (data.value) {
    item.value!.transcript = data.value.transcript
    item.value!.instruction = data.value.instruction
    selectedTab.value = 'transcript'
    useGlobalMessage('<b>ИИ</b>: расшифровка аудиозаписи успешно завершена', 'success')
  }
  transcribing.value = false
}

async function analyze() {
  if (transcribing.value || !item.value?.transcript) {
    return
  }
  analyzing.value = true
  const { data, error } = await useHttp<CallAnalyzeFields>(
    `calls/${item.value!.id}/analyze`,
    {
      method: 'POST',
    },
  )
  if (error.value) {
    setTimeout(() => useGlobalMessage(`<b>Ошибка ИИ (анализ)</b>: ${error.value!.data.message}`, 'error'), 100)
  }
  else if (data.value) {
    item.value!.summary = data.value.summary
    item.value!.analysis_1 = data.value.analysis_1
    item.value!.analysis_2 = data.value.analysis_2
    item.value!.caller_type = data.value.caller_type
    item.value!.instruction = data.value.instruction
    useGlobalMessage('<b>ИИ</b>: анализ разговора успешно выполнен', 'success')
  }
  analyzing.value = false
}

function decodeHtmlEntities(value: string): string {
  // Декодируем HTML-сущности, чтобы instruction/prompt отображались человекочитаемо.
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

function hasInstruction(type: CallInstructionType): boolean {
  return (item.value?.instruction?.[type]?.text || '').trim().length > 0
}

function openInstruction(type: CallInstructionType) {
  if (!hasInstruction(type)) {
    return
  }
  // Открываем диалог сразу на нужном этапе из контекстного меню.
  selectedInstructionType.value = type
  isInstructionDialogOpen.value = true
}

const { user } = useAuthStore()

if (![1, 5, 151].includes(user!.id)) {
  showError({
    statusCode: 404,
    statusMessage: 'Not found',
  })
}

nextTick(loadData)
</script>

<template>
  <UiLoader v-if="item === undefined" />
  <template v-else>
    <div class="panel">
      <div class="panel-info">
        <div>
          <h2 style="font-size: 28px" class="nowrap pt-1">
            {{ formatPhone(item.number) }}
          </h2>
        </div>

        <div>
          <div>
            дата
          </div>
          <div>
            {{ formatDateTime(item.created_at) }}
          </div>
        </div>

        <div>
          <div>
            тип звонка
          </div>
          <div v-if="item.user">
            <span v-if="item.type === 'incoming'" class="text-success">
              Входящий
            </span>
            <template v-else>
              <span v-if="item.answered_at" class="text-success">
                Исходящий
              </span>
              <span v-else class="text-gray">
                Не дозвонились
              </span>
            </template>
          </div>
          <div v-else-if="item.is_missed">
            <span v-if="item.is_missed_callback" class="text-deepOrange">
              Перезвонили
            </span>
            <span v-else class="text-error">
              Пропущенный
            </span>
          </div>
        </div>
        <div>
          <div>
            тип разговора
          </div>
          <div>
            <span v-if="item.caller_type">
              {{ CallerTypeLabel[item.caller_type] }}
            </span>
            <span v-else class="text-gray">
              не установлено
            </span>
          </div>
        </div>

        <div>
          <div>
            пользователь
          </div>
          <div>
            <span v-if="item.user">
              {{ formatName(item.user, 'initials') }}
            </span>
            <span v-else class="text-gray">
              не установлено
            </span>
          </div>
        </div>
        <!-- <div>
          <div>
            время
          </div>
          <div>
            <div v-if="item.answered_at">
              <CallDuration :item="item" />
            </div>
            <span v-else class="text-gray">
              –
            </span>
          </div>
        </div> -->
        <div>
          <div>
            собеседник
          </div>
          <div>
            <CallPerson :item="item.aon" />
          </div>
        </div>
      </div>
      <UiTabs v-model="selectedTab" :items="tabs" :counts="tabCounts">
        <CallPlayer :item="item" />
        <div class="page-calls-id__menu">
          <v-menu location="bottom">
            <template #activator="{ props }">
              <v-btn
                :size="42"
                :icon="mdiDotsHorizontal"
                v-bind="props"
                :loading="transcribing || analyzing || downloading"
              />
            </template>
            <v-list>
              <v-list-item :disabled="!item.has_recording || !item.recording_url" @click="downloadRecording()">
                скачать аудиозапись
              </v-list-item>
              <v-divider />
              <v-list-item @click="transcribe()">
                расшифровка аудиозаписи
              </v-list-item>
              <v-list-item :disabled="!hasInstruction('transcription')" @click="openInstruction('transcription')">
                инструкция
              </v-list-item>
              <v-divider />
              <v-list-item :disabled="!item.transcript" @click="analyze()">
                анализ разговора
              </v-list-item>
              <v-list-item :disabled="!hasInstruction('analysis')" @click="openInstruction('analysis')">
                инструкция
              </v-list-item>
            </v-list>
          </v-menu>
        </div>
      </UiTabs>
    </div>
    <div v-if="selectedTab === 'transcript'">
      <div
        v-if="item.transcript" class="container"
        :class="{ 'text-pre-wrap': !item.transcript.startsWith('<') }"
        v-html="item.transcript"
      />
      <UiNoData v-else />
    </div>
    <div v-else-if="selectedTab === 'summary'">
      <div v-if="item.summary" class="text-pre-wrap container" v-html="item.summary" />
      <UiNoData v-else />
    </div>
    <div v-else-if="selectedTab === 'analysis_1'">
      <div v-if="item.analysis_1" class="text-pre-wrap container" v-html="item.analysis_1" />
      <UiNoData v-else />
    </div>
    <div v-else-if="selectedTab === 'analysis_2'">
      <div v-if="item.analysis_2" class="text-pre-wrap container" v-html="item.analysis_2" />
      <UiNoData v-else />
    </div>
  </template>

  <v-dialog v-model="isInstructionDialogOpen" max-width="900">
    <div class="dialog-wrapper">
      <div class="dialog-header">
        <div>
          {{ selectedInstructionType === 'transcription' ? 'Расшифровка аудиозаписи' : 'Анализ разговора' }}
          <div class="dialog-subheader">
            {{ formatDateTime(selectedInstructionCreatedAt) }}
          </div>
        </div>
        <v-btn icon="$close" :size="48" variant="text" @click="isInstructionDialogOpen = false" />
      </div>
      <div v-if="selectedInstructionParts" class="dialog-body">
        <h2>
          Инструкция
        </h2>
        <div class="page-calls-id__ai-instruction-text">
          {{ selectedInstructionParts.instruction }}
        </div>

        <h2 class="mt-6">
          Промпт
        </h2>
        <div class="page-calls-id__ai-instruction-text">
          {{ selectedInstructionParts.prompt }}
        </div>
      </div>
    </div>
  </v-dialog>
</template>

<style lang="scss">
.page-calls-id {
  .container {
    max-width: 900px;

    p,
    li {
      font-weight: 400 !important;
    }

    p:not(:last-child) {
      margin-bottom: 20px;
    }

    b,
    strong {
      font-weight: bold !important;
    }

    & > h3 {
      font-weight: bold !important;

      &:not(:first-child) {
        margin-top: 20px !important;
      }
    }

    ul,
    ol {
      padding: 10px 0 10px 20px;
    }
  }

  .tabs {
    position: relative;
  }

  .call-player {
    padding-left: 20px;
    align-self: center;
  }

  &__ai-instruction-text {
    white-space: pre-wrap;
    word-break: break-word;
    // Синхронизируем шрифт с CodeMirror, чтобы текст в диалоге
    // визуально совпадал с редактором инструкции/промпта.
    font-family: 'ibm-plex', monospace;
    font-size: 14px;
    line-height: 21px;
  }

  &__menu {
    position: absolute;
    right: 16px;
    top: 3px;
  }
}
</style>
