<script setup lang="ts">
import type { CallListResource } from '~/components/Call'

type AiPipelineMode = 'transcribe_and_analyze' | 'analyze'

interface Filters {
  date_from?: string
  date_to?: string
}

interface RunAiPipelineResponse {
  total: number
  queued: number
}

const AVERAGE_JOB_SECONDS = 5
const WORKERS_BY_ENV: Record<string, number> = {
  local: 3,
  production: 10,
}
const DEFAULT_WORKERS = 3

const filters = ref<Filters>({})
const isRunning = ref(false)
const runtimeConfig = useRuntimeConfig()

const { items, total, indexPageData, reloadData } = useIndex<CallListResource>('calls', filters, {
  // На этой странице показываем только звонки, подходящие под AI-пайплайн.
  staticFilters: {
    should_run_ai_pipeline: true,
  },
  // Для этой страницы не сохраняем фильтры в localStorage.
  saveFilters: false,
})

const callsCount = computed(() => total.value ?? 0)
const estimatedWorkers = computed(() => {
  const env = runtimeConfig.public.env || 'local'
  return WORKERS_BY_ENV[env] ?? DEFAULT_WORKERS
})
const estimatedAnalyzeTime = computed(() =>
  formatEta(Math.ceil((callsCount.value * AVERAGE_JOB_SECONDS) / estimatedWorkers.value)),
)
const estimatedTranscribeAnalyzeTime = computed(() =>
  formatEta(Math.ceil((callsCount.value * AVERAGE_JOB_SECONDS * 2) / estimatedWorkers.value)),
)

const modeTitles: Record<AiPipelineMode, string> = {
  transcribe_and_analyze: 'транскрибация + анализ',
  analyze: 'только анализ',
}

function formatEta(totalSeconds: number): string {
  const totalMinutes = Math.ceil(totalSeconds / 60)

  if (totalMinutes < 60) {
    return `${totalMinutes} мин`
  }

  const hours = Math.floor(totalMinutes / 60)
  const minutes = totalMinutes % 60

  if (minutes === 0) {
    return `${hours} ч`
  }

  return `${hours} ч ${minutes} мин`
}

async function runAiPipeline(mode: AiPipelineMode) {
  if (isRunning.value || !callsCount.value) {
    return
  }

  if (callsCount.value > 1000) {
    useGlobalMessage(`Звонков в выборке должно быть не более <b>1 000</b>`, 'error')
    return
  }

  isRunning.value = true
  const { data, error } = await useHttp<RunAiPipelineResponse>('calls/run-ai-pipeline', {
    method: 'POST',
    body: {
      ...filters.value,
      mode,
    },
  })

  if (error.value) {
    const errorMessage = error.value.data?.message || 'Не удалось запустить AI-пайплайн'
    setTimeout(() => useGlobalMessage(`<b>Ошибка ИИ</b>: ${errorMessage}`, 'error'), 100)
    isRunning.value = false
    return
  }

  if (data.value) {
    const skipped = data.value.total - data.value.queued
    const skippedReason = mode === 'analyze' ? 'нет транскрипта/записи' : 'нет записи'
    const skippedText = skipped > 0 ? `, пропущено (${skippedReason}): ${skipped}` : ''
    useGlobalMessage(
      `<b>ИИ</b>: ${modeTitles[mode]} запущены. В очереди: ${data.value.queued}${skippedText}`,
      'success',
    )
  }

  isRunning.value = false
  reloadData()
}
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <UiDateInput v-model="filters.date_from" label="Дата от" density="comfortable" />
      <UiDateInput v-model="filters.date_to" label="Дата до" density="comfortable" />
      <div>
        <v-fade-transition>
          <span v-if="total !== undefined" class="text-gray">всего: {{ formatPrice(total) }}</span>
        </v-fade-transition>
      </div>
    </template>

    <template #buttons>
      <div class="page-calls-analysis__actions">
        <v-menu>
          <template #activator="{ props }">
            <v-btn
              class="page-calls-analysis__menu-btn"
              color="primary"
              :loading="isRunning"
              v-bind="props"
            >
              запуск AI
              <v-icon icon="$expand" class="ml-1 page-calls-analysis__menu-icon" />
            </v-btn>
          </template>

          <v-list>
            <v-list-item @click="runAiPipeline('transcribe_and_analyze')">
              <v-list-item-title>транскрибация + анализ</v-list-item-title>
              <v-list-item-subtitle> ~{{ estimatedTranscribeAnalyzeTime }} </v-list-item-subtitle>
            </v-list-item>
            <v-list-item @click="runAiPipeline('analyze')">
              <v-list-item-title>только анализ</v-list-item-title>
              <v-list-item-subtitle> ~{{ estimatedAnalyzeTime }} </v-list-item-subtitle>
            </v-list-item>
          </v-list>
        </v-menu>
      </div>
    </template>

    <CallList :items="items" clickable />
  </UiIndexPage>
</template>

<style lang="scss">
.page-calls-analysis {
  .filters__inputs {
    max-width: 100%;
  }

  &__actions {
    display: flex;
    align-items: center;
    gap: 16px;
  }

  &__menu-icon {
    transition: transform ease-in-out 0.2s;
  }

  &__menu-btn[aria-expanded='true'] {
    .page-calls-analysis__menu-icon {
      transform: rotate(-180deg);
    }
  }
}
</style>
