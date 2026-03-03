<script setup lang="ts">
import type { CallListResource } from '~/components/CallApp'
import { mdiAutoFix } from '@mdi/js'

interface CallResource extends CallListResource {
  analysis_1: string | null
  analysis_2: string | null
  analysis_3: string | null
}

interface CallAiFields {
  transcription: string
  summary: string
  analysis_1: string
  analysis_2: string
  analysis_3: string
}

type CallAiField = keyof CallAiFields

const improving = ref(false)
const route = useRoute()
const item = ref<CallResource>()
const { tabs, selectedTab, tabCounts } = useTabs({
  transcription: 'транскрибация',
  summary: 'краткое содержание',
  analysis1: 'анализ 1',
  analysis2: 'анализ 2',
  analysis3: 'анализ 3',
})

async function loadData() {
  const { data } = await useHttp<CallResource>(`calls/${route.params.id}`)
  item.value = data.value as CallResource
}

async function downloadRecording(e: MouseEvent) {
  e.stopPropagation()
  const audio = await getAudio('download')
  const link = document.createElement('a')
  link.href = audio
  link.click()
}

async function getAudio(action: 'play' | 'download') {
  const { data } = await useHttp(
    `calls/recording/${action}/${item.value!.id}`,
  )
  return data.value as string
}

async function improve() {
  improving.value = true
  const { data, error } = await useHttp<CallAiFields>(
    `calls/${item.value!.id}/improve`,
    {
      method: 'POST',
    },
  )
  if (error.value) {
    setTimeout(() => useGlobalMessage(`<b>Ошибка ИИ</b>: ${error.value!.data.message}`, 'error'), 100)
  }
  if (data.value) {
    for (const f in data.value) {
      const field = f as CallAiField
      item.value![field] = data.value[field]
    }
    useGlobalMessage('<b>ИИ</b>: обработка записи прошла успешно', 'success')
  }
  improving.value = false
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
                Без ответа
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
            пользователь
          </div>
          <div>
            <span v-if="item.user">
              {{ formatName(item.user) }}
            </span>
            <span v-else class="text-gray">
              не установлено
            </span>
          </div>
        </div>
        <div>
          <div>
            продолжительность
          </div>
          <div>
            <div v-if="item.answered_at">
              <CallAppDuration :item="item" />
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
          <div>
            <CallAppPerson :item="item.aon" class="text-truncate" />
          </div>
        </div>
        <!-- <div class="panel-actions">
          <CallAppDownload v-if="item.has_recording" :item="item" />
        </div> -->
      </div>
      <UiTabs v-model="selectedTab" :items="tabs" :counts="tabCounts">
        <div :class="{ 'tabs-item--disabled': !item.has_recording }" class="tabs-item" @click="downloadRecording">
          скачать аудиозапись
        </div>
        <v-btn
          v-if="item.has_recording"
          :icon="mdiAutoFix"
          :size="42"
          class="page-calls-id__improve"
          :loading="improving"
          @click="improve()"
        />
      </UiTabs>
    </div>
    <div v-if="selectedTab === 'transcription'">
      <div v-if="item.transcription" class="container text-pre-wrap" v-html="item.transcription" />
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

  &__improve {
    position: absolute;
    right: 16px;
    top: 3px;
  }
}
</style>
