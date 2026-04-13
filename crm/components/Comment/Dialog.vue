<script setup lang="ts">
import type { CommentTab } from '.'
import { mdiSendVariant } from '@mdi/js'
import { format } from 'date-fns'

type AutoSuggestState = 'none' | 'processing' | 'ready'

interface AutoSuggestCallResource {
  id: number
  summary: string | null
}

interface CallSummaryUpdatedPayload {
  id: number | string
  summary: string | null
  user_id: number | null
}

const {
  entityId,
  entityType,
  extra: extraProp,
} = defineProps<{
  entityId: number
  entityType: EntityType

  /**
   * Записывает в поле comments.extra текущий url страницы
   */
  extra?: string
}>()
const emit = defineEmits<{ (e: 'created'): void }>()
const { dialog, width, transition } = useDialog('default')
const comments = ref<CommentResource[]>([])
const input = ref()
const editInput = ref()
const wrapper = ref<HTMLDivElement | null>(null)
const text = ref('')
const noScroll = ref(false)
const isLoaded = ref(false)
const editId = ref<number>()
// Храним только id звонка-кандидата для auto-suggest.
const autoSuggestCallId = ref<number | null>(null)
const autoSuggestState = ref<AutoSuggestState>('none')
const isAutoSuggestLoading = ref(false)
const isSendingAutoSuggest = ref(false)
const { user, isAdmin } = useAuthStore()
const { $addSseListener, $removeSseListener } = useNuxtApp()
const isCallSummaryListenerBound = ref(false)
// у клиента всегда показываем вкладки
const tabs = entityType === EntityTypeValue.client
const isTabCountsLoaded = ref(false)
let extra: string | undefined = extraProp

const selectedTab = ref<CommentTab>((extra || '') as CommentTab)
const tabCounts = ref<Record<CommentTab, number>>()
const hasText = computed(() => text.value.length > 0)
const isAutoSuggestProcessing = computed(() => autoSuggestState.value === 'processing')
const isAutoSuggestReady = computed(() => autoSuggestState.value === 'ready')
const showSendButton = computed(() => !editId.value && hasText.value)
const showAutoSuggestButton = computed(
  () => !editId.value && !hasText.value && autoSuggestState.value !== 'none',
)

function isSummaryReady(summary: string | null | undefined): boolean {
  return (summary || '').trim().length > 0
}

function setAutoSuggestStateFromSummary(summary: string | null | undefined) {
  autoSuggestState.value = isSummaryReady(summary) ? 'ready' : 'processing'
}

function onCallSummaryUpdated(payload: CallSummaryUpdatedPayload) {
  // Обновляем только активный диалог и только его звонок-кандидат.
  if (!dialog.value || autoSuggestCallId.value === null) {
    return
  }

  const payloadCallId = Number(payload.id)
  if (Number.isNaN(payloadCallId) || payloadCallId !== autoSuggestCallId.value) {
    return
  }

  if (!isSummaryReady(payload.summary)) {
    return
  }

  autoSuggestState.value = 'ready'
}

function bindCallSummaryListener() {
  if (isCallSummaryListenerBound.value) {
    return
  }

  $addSseListener('CallSummaryUpdatedEvent', onCallSummaryUpdated)
  isCallSummaryListenerBound.value = true
}

function unbindCallSummaryListener() {
  if (!isCallSummaryListenerBound.value) {
    return
  }

  $removeSseListener('CallSummaryUpdatedEvent')
  isCallSummaryListenerBound.value = false
}

function scrollBottom() {
  nextTick(() => {
    wrapper.value?.scrollTo({ top: 99999, behavior: noScroll.value ? 'instant' : 'smooth' })
    input.value?.focus()
  })
}

function finalizeLoadedView() {
  noScroll.value = true
  scrollBottom()
  setTimeout(() => {
    noScroll.value = false
    isLoaded.value = true
  }, 200)
}

async function open(autoSuggestEnabled = false) {
  dialog.value = true
  editId.value = undefined
  autoSuggestCallId.value = null
  autoSuggestState.value = 'none'
  isLoaded.value = false

  if (tabs && !isTabCountsLoaded.value) {
    watch(selectedTab, async (newVal) => {
      extra = newVal || ''
      autoSuggestCallId.value = null
      autoSuggestState.value = 'none'
      isLoaded.value = false
      await loadData()
      finalizeLoadedView()
    })

    await loadTabCounts()
    isTabCountsLoaded.value = true
  }

  await loadData()
  if (autoSuggestEnabled) {
    await loadAutoSuggest()
  }
  finalizeLoadedView()
}

function handleCommentError(errorData: any, fallbackMessage: string) {
  const messageFromField = errorData?.errors?.text?.[0]
  const message = messageFromField || errorData?.message || fallbackMessage

  useGlobalMessage(message, 'error')
}

function handleAutoSuggestError(errorData: any) {
  const message
    = errorData?.errors?.call_id?.[0]
      || errorData?.errors?.summary?.[0]
      || errorData?.errors?.text?.[0]
      || errorData?.message
      || 'Не удалось подтянуть подсказку'

  useGlobalMessage(message, 'error')
}

async function createComment(payload: { text: string }) {
  // Единая отправка комментария: и обычного, и принятого из auto-suggest.
  const { data, error } = await useHttp<CommentResource>(`comments`, {
    method: 'post',
    body: {
      extra,
      text: payload.text,
      entity_id: entityId,
      entity_type: entityType,
    },
  })

  if (error.value) {
    handleCommentError(error.value.data, 'Не удалось отправить комментарий')
    return false
  }

  if (!data.value) {
    return false
  }

  noScroll.value = true
  comments.value.push(data.value)
  // После отправки любого комментария сбрасываем локальный флаг auto-suggest.
  autoSuggestCallId.value = null
  autoSuggestState.value = 'none'
  text.value = ''

  if (tabs && tabCounts.value) {
    if (selectedTab.value in tabCounts.value) {
      tabCounts.value[selectedTab.value]++
    }
    else {
      tabCounts.value = {
        ...tabCounts.value,
        [selectedTab.value]: 1,
      }
    }
  }

  scrollBottom()
  emit('created')
  setTimeout(() => (noScroll.value = false), 200)

  return true
}

async function send() {
  if (editId.value) {
    await saveEdit()
    return
  }
  if (!text.value.length) {
    return
  }
  await createComment({ text: text.value })
}

async function loadData() {
  const { data } = await useHttp<ApiResponse<CommentResource>>(`comments`, {
    params: {
      extra,
      entity_id: entityId,
      entity_type: entityType,
    },
  })
  comments.value = data.value?.data ?? []
}

async function loadTabCounts() {
  if (!tabs) {
    return
  }
  const { data } = await useHttp<Record<CommentTab, number>>(`comments`, {
    params: {
      entity_id: entityId,
      entity_type: entityType,
      tab_counts: 1,
    },
  })

  tabCounts.value = data.value!
}

async function loadAutoSuggest() {
  // Подгружаем последний звонок-кандидат для кнопки "подтянуть подсказку".
  isAutoSuggestLoading.value = true
  const { data, error } = await useHttp<AutoSuggestCallResource | null>('comments/auto-suggest', {
    params: {
      entity_id: entityId,
      entity_type: entityType,
    },
  })

  if (error.value) {
    autoSuggestCallId.value = null
    autoSuggestState.value = 'none'
    isAutoSuggestLoading.value = false
    return
  }

  if (!data.value) {
    autoSuggestCallId.value = null
    autoSuggestState.value = 'none'
    isAutoSuggestLoading.value = false
    return
  }

  autoSuggestCallId.value = data.value.id
  setAutoSuggestStateFromSummary(data.value.summary)
  isAutoSuggestLoading.value = false
}

async function pullAutoSuggestText() {
  if (!autoSuggestCallId.value || !isAutoSuggestReady.value || isSendingAutoSuggest.value) {
    return
  }

  isSendingAutoSuggest.value = true
  const { data, error } = await useHttp<{ text: string }>('comments/auto-suggest/text', {
    method: 'post',
    body: {
      entity_id: entityId,
      entity_type: entityType,
      call_id: autoSuggestCallId.value,
    },
  })

  if (error.value) {
    handleAutoSuggestError(error.value.data)
    isSendingAutoSuggest.value = false
    return
  }

  const suggestText = (data.value?.text || '').trim()
  if (!suggestText.length) {
    useGlobalMessage('Текст подсказки пустой', 'error')
    isSendingAutoSuggest.value = false
    return
  }

  text.value = suggestText
  nextTick(() => input.value?.focus())
  isSendingAutoSuggest.value = false
}

function destroy(c: CommentResource) {
  const index = comments.value.findIndex(e => e.id === c.id)
  comments.value.splice(index, 1)
  useHttp<CommentResource>(`comments/${c.id}`, {
    method: 'delete',
  })
}

function startEdit(c: CommentResource) {
  editId.value = c.id
  text.value = c.text
  nextTick(() => editInput.value?.focus())
}

async function saveEdit() {
  if (!text.value.length) {
    return
  }
  const index = comments.value.findIndex(e => e.id === editId.value)
  const { data } = await useHttp<CommentResource>(`comments/${editId.value}`, {
    method: 'put',
    body: {
      text: text.value,
    },
  })
  comments.value.splice(index, 1, data.value!)
  quitEdit()
}

function quitEdit() {
  editId.value = undefined
  text.value = ''
}

watch(dialog, (isOpen) => {
  // Слушаем live-обновления только пока открыт конкретный диалог.
  if (isOpen) {
    bindCallSummaryListener()
  }
  else {
    unbindCallSummaryListener()
  }

  if (entityType !== EntityTypeValue.request) {
    return
  }
  const el = document.documentElement.querySelector(`#request-${entityId}`)

  if (isOpen) {
    el?.classList.add('is-comment-editing')
  }
  else {
    el?.classList.remove('is-comment-editing')
  }
})

onUnmounted(() => unbindCallSummaryListener())

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width" :transition="transition">
    <div
      ref="wrapper"
      class="dialog-wrapper comments-wrapper"
      :class="{
        'comments-wrapper--no-scroll': noScroll,
        'comments-wrapper--loaded': isLoaded,
      }"
    >
      <v-fade-transition>
        <UiLoader v-if="!isLoaded" />
      </v-fade-transition>
      <CommentTabs v-if="isTabCountsLoaded" v-model="selectedTab" :counts="tabCounts" />
      <transition-group name="new-comment" class="comments__items" tag="div">
        <div v-for="c in comments" :key="c.id" class="comment">
          <UiAvatar :item="c.user" :size="46" />
          <div>
            <div class="comment__title">
              <span>
                {{ formatName(c.user) }}
                <v-menu v-if="c.user.id === user?.id">
                  <template #activator="{ props }">
                    <v-icon icon="$more" v-bind="props" />
                  </template>
                  <v-list density="comfortable">
                    <v-list-item @click="startEdit(c)"> редактировать </v-list-item>
                    <v-list-item @click="destroy(c)"> удалить </v-list-item>
                  </v-list>
                </v-menu>
              </span>

              <span v-if="c.created_at">
                {{ format(c.created_at, 'dd.MM.yy в HH:mm') }}
              </span>
            </div>
            <div class="comment__text">
              {{ c.text }}
            </div>
          </div>
        </div>
      </transition-group>
      <div v-if="isAdmin" class="comments__input">
        <template v-if="editId">
          <span class="text-gray">Редактирование комментария</span>
          <v-textarea
            ref="editInput"
            v-model="text"
            rows="1"
            placeholder="Введите комментарий..."
            auto-grow
            hide-details
            maxlength="1000"
            max-height="100"
            @keydown.enter.exact.prevent
            @keyup.enter.exact="saveEdit()"
            @keydown.esc.prevent.stop="quitEdit()"
          />
        </template>
        <v-textarea
          v-else
          ref="input"
          v-model="text"
          rows="1"
          placeholder="Введите комментарий..."
          auto-grow
          hide-details
          maxlength="1000"
          max-height="100"
          @keydown.enter.exact.prevent
          @keyup.enter.exact="send()"
        />
        <transition name="comment-btn">
          <v-btn
            v-if="showSendButton"
            :icon="mdiSendVariant"
            height="48"
            width="48"
            variant="text"
            color="secondary"
            @click="send()"
          />
          <v-tooltip
            v-else-if="showAutoSuggestButton && !isAutoSuggestLoading"
            location="left"
            :disabled="!isAutoSuggestProcessing"
          >
            <template #activator="{ props }">
              <span v-bind="props">
                <v-btn
                  :icon="mdiSendVariant"
                  height="48"
                  width="48"
                  variant="text"
                  :color="isAutoSuggestProcessing ? 'grey' : 'warning'"
                  :class="{ 'no-pointer-events opacity-5': isAutoSuggestProcessing }"
                  :loading="isSendingAutoSuggest"
                  @click="pullAutoSuggestText()"
                />
              </span>
            </template>
            <span>Звонок обрабатывается, подождите...</span>
          </v-tooltip>
        </transition>
      </div>
      <UiNoData v-else-if="isLoaded && comments.length === 0" />
    </div>
  </v-dialog>
</template>
