<script setup lang="ts">
import type { CallerType, CallListResource } from '~/components/Call'
import { mdiAutoFix } from '@mdi/js'
import { CallerTypeLabel } from '~/components/Call'

type CallInstructionType = 'transcription' | 'analysis'

interface CallInstructionFields {
  transcription: string | null
  analysis: string | null
}

interface CallResource extends CallListResource {
  analysis_1: string | null
  analysis_2: string | null
  analysis_3: string | null
  instruction: CallInstructionFields | null
}

interface CallTranscribeFields {
  transcript: string
  instruction: CallInstructionFields
}

interface CallAnalyzeFields {
  summary: string
  analysis_1: string
  analysis_2: string
  analysis_3: string
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
  analysis1: 'анализ 1',
  analysis2: 'анализ 2',
  analysis3: 'анализ 3',
})

const selectedInstructionRaw = computed<string>(() => {
  return item.value?.instruction?.[selectedInstructionType.value] || ''
})

const selectedInstructionParts = computed(() => {
  const [instructionRaw = '', promptRaw = ''] = selectedInstructionRaw.value.split('<USER_PROMPT>')

  return {
    instruction: decodeHtmlEntities(instructionRaw.trim()),
    prompt: decodeHtmlEntities(promptRaw.trim()),
  }
})

async function loadData() {
  const { data } = await useHttp<CallResource>(`calls/${route.params.id}`)
  item.value = data.value as CallResource
}

async function downloadRecording(e: MouseEvent) {
  downloading.value = true
  e.stopPropagation()
  try {
    const audio = await getAudio('download')
    const link = document.createElement('a')
    link.href = audio
    link.click()
  }
  finally {
    setTimeout(() => (downloading.value = false), 300)
  }
}

async function getAudio(action: 'play' | 'download') {
  const { data } = await useHttp(
    `calls/recording/${action}/${item.value!.id}`,
  )
  return data.value as string
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
    useGlobalMessage('<b>ИИ</b>: транскрибация аудиозаписи успешно завершена', 'success')
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
    item.value!.analysis_3 = data.value.analysis_3
    item.value!.caller_type = data.value.caller_type
    item.value!.instruction = data.value.instruction
    selectedTab.value = 'summary'
    useGlobalMessage('<b>ИИ</b>: анализ транскрипта успешно выполнен', 'success')
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

function openInstruction(type: CallInstructionType) {
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
        <div>
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
        </div>
        <div>
          <div>
            собеседник
          </div>
          <div class="text-truncate" style="max-width: 200px">
            <CallPerson :item="item.aon" class="text-truncate" />
          </div>
        </div>
      </div>
      <UiTabs v-model="selectedTab" :items="tabs" :counts="tabCounts">
        <div :class="{ 'tabs-item--disabled': !item.has_recording }" class="tabs-item" @click="downloadRecording">
          скачать аудиозапись
        </div>
        <div class="page-calls-id__actions">
          <v-menu location="bottom">
            <template #activator="{ props }">
              <v-btn :size="42" :icon="mdiAutoFix" v-bind="props" :loading="transcribing || analyzing" />
            </template>
            <v-list>
              <v-list-item @click="transcribe()">
                расшифровка аудиозаписи
              </v-list-item>
              <v-list-item :disabled="!item.instruction?.transcription" @click="openInstruction('transcription')">
                инструкция
              </v-list-item>
              <v-divider />
              <v-list-item :disabled="!item.transcript" @click="analyze()">
                анализ разговора
              </v-list-item>
              <v-list-item :disabled="!item.instruction?.analysis" @click="openInstruction('analysis')">
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
    <div v-else-if="selectedTab === 'analysis1'">
      <div v-if="item.analysis_1" class="text-pre-wrap container" v-html="item.analysis_1" />
      <UiNoData v-else />
    </div>
    <div v-else-if="selectedTab === 'analysis2'">
      <div v-if="item.analysis_2" class="text-pre-wrap container" v-html="item.analysis_2" />
      <UiNoData v-else />
    </div>
    <div v-else-if="selectedTab === 'analysis3'">
      <div v-if="item.analysis_3" class="text-pre-wrap container" v-html="item.analysis_3" />
      <UiNoData v-else />
    </div>
  </template>

  <v-dialog v-model="isInstructionDialogOpen" max-width="900">
    <div class="dialog-wrapper">
      <div class="dialog-header">
        {{ selectedInstructionType === 'transcription' ? 'Расшифровка аудиозаписи' : 'Анализ разговора' }}
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

  &__actions {
    position: absolute;
    right: 16px;
    top: 3px;
    display: flex;
    gap: 8px;
  }

  &__ai-instruction-text {
    white-space: pre-wrap;
    word-break: break-word;
  }
}
</style>
