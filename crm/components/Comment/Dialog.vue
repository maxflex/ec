<script setup lang="ts">
import type { CommentTab } from '.'
import { mdiSendVariant } from '@mdi/js'
import { format } from 'date-fns'

const { entityId, entityType, extra: extraProp } = defineProps<{
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
const { user, isAdmin } = useAuthStore()
// у клиента всегда показываем вкладки
const tabs = entityType === EntityTypeValue.client
const isTabCountsLoaded = ref(false)
let extra: string | undefined = extraProp

const selectedTab = ref<CommentTab>((extra || '') as CommentTab)
const tabCounts = ref<Record<CommentTab, number>>()

function scrollBottom() {
  nextTick(() => {
    console.log(wrapper.value)
    wrapper.value?.scrollTo({ top: 99999, behavior: noScroll.value ? 'instant' : 'smooth' })
    input.value?.focus()
  })
}

async function open() {
  dialog.value = true
  editId.value = undefined

  if (tabs && !isTabCountsLoaded.value) {
    watch(selectedTab, (newVal) => {
      extra = newVal || ''
      loadData()
    })

    await loadTabCounts()
    isTabCountsLoaded.value = true
  }

  await loadData()
}

async function send() {
  if (editId.value) {
    await saveEdit()
    return
  }
  if (!text.value.length) {
    return
  }
  const { data } = await useHttp<CommentResource>(
    `comments`,
    {
      method: 'post',
      body: {
        extra,
        text: text.value,
        entity_id: entityId,
        entity_type: entityType,
      },
    },
  )
  if (data.value) {
    noScroll.value = true
    comments.value.push(data.value)
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
    setTimeout(() => noScroll.value = false, 200)
  }
}

async function loadData() {
  isLoaded.value = false
  const { data } = await useHttp<ApiResponse<CommentResource>>(
    `comments`,
    {
      params: {
        extra,
        entity_id: entityId,
        entity_type: entityType,
      },
    },
  )
  if (data.value) {
    noScroll.value = true
    comments.value = data.value.data
    scrollBottom()
    setTimeout(() => {
      noScroll.value = false
      isLoaded.value = true
    }, 200)
  }
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
  console.log('tabCounts loaded', data.value!)
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

defineExpose({ open })
</script>

<template>
  <v-dialog
    v-model="dialog"
    :width="width"
    :transition="transition"
  >
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
      <transition-group
        name="new-comment"
        class="comments__items"
        tag="div"
      >
        <div
          v-for="c in comments"
          :key="c.id"
          class="comment"
        >
          <UiAvatar
            :item="c.user"
            :size="46"
          />
          <div>
            <div class="comment__title">
              <span>
                {{ formatName(c.user) }}
                <v-menu v-if="c.user.id === user?.id">
                  <template #activator="{ props }">
                    <v-icon icon="$more" v-bind="props" />
                  </template>
                  <v-list density="comfortable">
                    <v-list-item @click="startEdit(c)">
                      редактировать
                    </v-list-item>
                    <v-list-item @click="destroy(c)">
                      удалить
                    </v-list-item>
                  </v-list>
                </v-menu>
              </span>

              <span v-if="c.created_at">
                {{ format(c.created_at, 'dd.MM.yy в HH:mm') }}
              </span>
            </div>
            {{ c.text }}
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
            v-if="text.length > 0"
            :icon="mdiSendVariant"
            height="48"
            width="48"
            variant="text"
            color="secondary"
            @click="send()"
          />
        </transition>
      </div>
      <UiNoData v-else-if="isLoaded && comments.length === 0" />
    </div>
  </v-dialog>
</template>
