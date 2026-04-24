<script setup lang="ts">
interface ChatMessage {
  id: number
  role: 'user' | 'assistant'
  text: string
}

interface CallsChatResponse {
  intent: 'sql_search' | 'context_answer' | 'direct_answer'
  answer: string
  sql_query: string | null
  rows_count: number
}

interface CallsChatRequestMessage {
  role: 'user' | 'assistant'
  text: string
}

const { user } = useAuthStore()

// Ограничиваем доступ к странице тем же whitelist, что и в /calls/[id].
if (![1, 5, 151].includes(user!.id)) {
  showError({
    statusCode: 404,
    statusMessage: 'Not found',
  })
}

const suggestions: string[] = [
  'Часто ли клиенты жалуются, что не работает Telegram?',
  'Какие самые частые слова-паразиты у менеджеров?',
  'Найди интересные нестандартные звонки',
  'У кого из менеджеров самые длинные разговоры?',
  'Найди звонки, где клиент был недоволен или предъявлял претензии',
]

const draft = ref('')
const isSending = ref(false)
const isGenerating = ref(false)
const inputRef = ref<HTMLTextAreaElement | null>(null)
const messages = ref<ChatMessage[]>([])

const MAX_INPUT_HEIGHT = 140

const isConversation = computed(() => messages.value.length > 0 || isGenerating.value)
const canSend = computed(() => draft.value.trim().length > 0 && !isSending.value)
let stopConversationWatch: (() => void) | null = null

// Подгоняем высоту поля под контент, чтобы переносы строк отображались сразу.
function resizeInput(): void {
  if (!inputRef.value) {
    return
  }

  inputRef.value.style.height = 'auto'
  const nextHeight = Math.min(inputRef.value.scrollHeight, MAX_INPUT_HEIGHT)
  inputRef.value.style.height = `${nextHeight}px`
  inputRef.value.style.overflowY
    = inputRef.value.scrollHeight > MAX_INPUT_HEIGHT ? 'auto' : 'hidden'
}

// Выносим фокус в отдельную функцию, чтобы переиспользовать в нескольких сценариях.
function focusInput(): void {
  inputRef.value?.focus()
}

function fillSuggestion(text: string): void {
  draft.value = text
  nextTick(() => {
    resizeInput()
    focusInput()
  })
}

function buildRequestMessages(): CallsChatRequestMessage[] {
  // Передаем всю текущую историю, чтобы backend мог обработать follow-up запросы.
  return messages.value.map(message => ({
    role: message.role,
    text: message.text,
  }))
}

async function sendMessage(): Promise<void> {
  if (!canSend.value) {
    return
  }

  const text = draft.value.trim()
  const historyMessages = buildRequestMessages()
  isSending.value = true

  messages.value.push({
    id: Date.now(),
    role: 'user',
    text,
  })

  draft.value = ''
  await nextTick()
  resizeInput()
  smoothScroll('main', 'bottom', 'auto')

  isGenerating.value = true
  await nextTick()
  smoothScroll('main', 'bottom', 'auto')

  try {
    const { data, error } = await useHttp<CallsChatResponse>('calls/chat', {
      method: 'POST',
      body: {
        prompt: text,
        messages: historyMessages,
      },
    })

    if (error.value) {
      const errorMessage = error.value.data?.message || 'Не удалось получить ответ от AI-чата'
      useGlobalMessage(errorMessage, 'error')
      messages.value.push({
        id: Date.now() + 1,
        role: 'assistant',
        text: 'Не удалось получить ответ. Попробуйте еще раз.',
      })
      return
    }

    const answer = data.value?.answer?.trim() || 'По запросу пока нет ответа.'
    messages.value.push({
      id: Date.now() + 1,
      role: 'assistant',
      text: answer,
    })
  }
  finally {
    isSending.value = false
    isGenerating.value = false
    await nextTick()
    smoothScroll('main', 'bottom', 'auto')
  }
}

function onInputKeydown(event: KeyboardEvent): void {
  // Enter отправляет вопрос, Shift+Enter добавляет новую строку.
  if (event.key === 'Enter' && !event.shiftKey) {
    event.preventDefault()
    void sendMessage()
    return
  }

  nextTick(resizeInput)
}

function syncMainConversationClass(): void {
  const main = document.querySelector('main.page-chat')
  if (!(main instanceof HTMLElement)) {
    return
  }

  main.classList.toggle('page-chat--conversation', isConversation.value)
}

onMounted(() => {
  resizeInput()
  stopConversationWatch = watch(isConversation, syncMainConversationClass, { immediate: true })
  // Сразу ставим курсор в поле ввода при открытии страницы чата.
  nextTick(focusInput)
})

onBeforeUnmount(() => {
  stopConversationWatch?.()

  const main = document.querySelector('main.page-chat')
  if (main instanceof HTMLElement) {
    main.classList.remove('page-chat--conversation')
  }
})
</script>

<template>
  <header class="chat__hero" :class="{ 'chat__hero--hidden': isConversation }">
    <h1 class="chat__title">
      Чат по звонкам
    </h1>
    <p class="chat__subtitle">
      Задайте вопрос в свободной форме
    </p>
  </header>

  <section class="chat__thread" :class="{ 'chat__thread--active': isConversation }">
    <article
      v-for="message in messages"
      :key="message.id"
      class="chat__row"
      :class="`chat__row--${message.role}`"
    >
      <div v-if="message.role === 'user'" :class="`chat__message chat__message--${message.role}`">
        {{ message.text }}
      </div>
      <div v-else :class="`chat__message chat__message--${message.role}`" v-html="message.text" />
    </article>

    <article v-if="isGenerating" class="chat__row chat__row--assistant chat__row-loading">
      <v-progress-circular :size="26" :width="2" indeterminate />
      Генерирую ответ...
    </article>
  </section>

  <footer class="chat__dock">
    <div class="chat__composer">
      <textarea
        ref="inputRef"
        v-model="draft"
        class="chat__input"
        rows="1"
        placeholder="Вопрос по звонкам..."
        @input="resizeInput"
        @keydown="onInputKeydown"
      />

      <v-btn
        color="primary"
        size="large"
        rounded="pill"
        min-width="130"
        :disabled="!canSend"
        @click="sendMessage"
      >
        Отправить
      </v-btn>
    </div>

    <div v-if="!isConversation" class="chat__suggestions">
      <v-chip
        v-for="suggestion in suggestions"
        :key="suggestion"
        @click="fillSuggestion(suggestion)"
      >
        {{ suggestion }}
      </v-chip>
    </div>
  </footer>
</template>

<style lang="scss">
.page-chat {
  --chat-content-width: 920px;
  position: relative;
  min-height: 100vh;
  background: rgb(var(--v-theme-bg));
  transition: background-color linear 0.2s;

  &--conversation {
    background-color: white;
    &:after {
      content: '';
      position: fixed;
      inset: 0;
      z-index: 0;
      $height: 80px;
      height: $height;
      top: calc(100vh - #{$height});

      /* Базовый слой: практически без blur там, где контент только входит под header. */
      backdrop-filter: saturate(108%) blur(1px);
      -webkit-backdrop-filter: saturate(108%) blur(1px);

      // background: linear-gradient(to top, rgb(var(--v-theme-bg)), rgba(var(--v-theme-bg), 0.5));
      background: linear-gradient(to top, white, rgba(white, 0.5));
    }
  }
}

.chat {
  &__hero,
  &__thread,
  &__dock {
    width: min(var(--chat-content-width), 100%);
    margin-inline: auto;
  }

  &__hero {
    text-align: center;
  }

  &__hero--hidden {
    opacity: 0;
    pointer-events: none;
    position: absolute;
    inset: 0 auto auto 0;
    height: 0;
    overflow: hidden;
    margin: 0;
  }

  &__title {
    margin: 0;
    font-size: 42px;
    font-weight: 700;
    line-height: 1.05;
    color: #212738;
  }

  &__subtitle {
    margin: 12px 0 0;
    font-size: 16px;
    color: #6f7788;
  }

  &__thread {
    display: flex;
    flex-direction: column;
    gap: 40px;
    overflow: visible;
  }

  &__thread:not(&__thread--active) {
    display: none;
  }

  &__row {
    display: flex;
    width: 100%;

    &-loading {
      gap: 10px;
      align-items: center;
    }
  }

  &__message {
    &--user {
      max-width: 72%;
      padding: 12px 14px;
      border-radius: 16px;
      background-color: #e9eef9;
      border: 1px solid #d4ddf2;
      color: #222a3d;
      line-height: 1.35;
      white-space: pre-wrap;
      overflow-wrap: anywhere;
    }

    &--assistant {
      max-width: min(860px, 100%);
      padding: 0;
      color: #222a3d;
      font-size: 17px;
      line-height: 1.45;
      white-space: normal;
      overflow-wrap: anywhere;

      // Нормализуем отступы html-контента из v-html, чтобы вложенные списки
      // выглядели аккуратно и предсказуемо.
      > :first-child {
        margin-top: 0;
      }

      > :last-child {
        margin-bottom: 0;
      }

      p {
        margin: 0 0 14px;
      }

      ul,
      ol {
        margin: 8px 0 14px;
        padding-left: 26px;
      }

      ul {
        list-style-type: disc;
      }

      ol {
        list-style-type: decimal;
      }

      li {
        margin: 6px 0;
      }

      li > ul,
      li > ol {
        margin-top: 8px;
        margin-bottom: 10px;
        padding-left: 22px;
      }

      ul ul {
        list-style-type: circle;
      }

      ul ul ul {
        list-style-type: square;
      }
    }
  }

  &__row--user {
    justify-content: flex-end;
  }

  &__row--assistant {
    justify-content: flex-start;
  }

  &__composer {
    width: 100%;
    display: flex;
    gap: 12px;
    align-items: flex-start;
    padding: 14px 16px;
    border-radius: 26px;
    border: 1px solid #dfe4ee;
    background-color: rgba(white, 0.95);
    backdrop-filter: saturate(108%) blur(1px);
    box-shadow: 0 8px 28px rgba(17, 24, 39, 0.06);
  }

  &__input {
    flex: 1;
    display: block;
    margin: 0;
    padding: 0;
    min-height: 24px;
    max-height: 140px;
    border: 0;
    outline: 0;
    resize: none;
    background: transparent;
    font-size: 16px;
    line-height: 1.35;
    color: #1f2433;

    &::placeholder {
      color: #8690a5;
    }
  }

  &__suggestions {
    width: 100%;
    margin-top: 30px;
    display: flex;
    column-gap: 10px;
    row-gap: 10px;
    justify-content: center;
    flex-wrap: wrap;
  }
}

main.page-chat:not(.page-chat--conversation) {
  justify-content: center;
  align-items: center;
}

main.page-chat:not(.page-chat--conversation) .chat__hero {
  margin: 0 0 30px;
}

main.page-chat.page-chat--conversation {
  align-items: stretch;

  .chat__thread {
    // Поток сообщений занимает доступную высоту и стабильно
    // отдает место доку снизу без скачков при появлении скролла.
    flex: 1 1 auto;
    padding: 30px 0 60px;
  }

  .chat__dock {
    position: sticky;
    bottom: 20px;
    z-index: 1;
  }
}
</style>
