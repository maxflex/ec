<script setup lang="ts">
import { mdiSendVariant } from '@mdi/js'

const { entityId, entityType } = defineProps<{
  entityId: number
  entityType: EntityString
}>()
const emit = defineEmits<{ (e: 'created'): void }>()
const { dialog, width } = useDialog('default')
const comments = ref<CommentResource[]>([])
const input = ref()
const wrapper = ref<HTMLDivElement | null>(null)
const text = ref('')
const noScroll = ref(false)
const loaded = ref(false)

function scrollBottom() {
  nextTick(() => {
    console.log(wrapper.value)
    wrapper.value?.scrollTo({ top: 99999, behavior: noScroll.value ? 'instant' : 'smooth' })
    input.value.focus()
  })
}

function open() {
  dialog.value = true
  loadData()
}

async function send() {
  if (!text.value.length) {
    return
  }
  const { data } = await useHttp<CommentResource>('comments', {
    method: 'post',
    body: {
      text: text.value,
      entity_id: entityId,
      entity_type: EntityTypeValue[entityType],
    },
  })
  if (data.value) {
    noScroll.value = true
    comments.value.push(data.value)
    text.value = ''
    scrollBottom()
    emit('created')
    setTimeout(() => noScroll.value = false, 200)
  }
}

async function loadData() {
  const { data } = await useHttp<ApiResponse<CommentResource[]>>('comments', {
    params: {
      entity_id: entityId,
      entity_type: EntityTypeValue[entityType],
    },
  })
  if (data.value) {
    noScroll.value = true
    comments.value = data.value.data
    scrollBottom()
    setTimeout(() => {
      noScroll.value = false
      loaded.value = true
    }, 200)
  }
}

defineExpose({ open })
</script>

<template>
  <v-dialog
    v-model="dialog"
    :width="width"
  >
    <div
      ref="wrapper"
      class="dialog-wrapper comments-wrapper"
      :class="{
        'comments-wrapper--no-scroll': noScroll,
        'comments-wrapper--loaded': loaded,
      }"
    >
      <v-fade-transition>
        <UiLoader v-if="!loaded" />
      </v-fade-transition>
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
              </span>
              <span v-if="c.created_at">
                {{ formatDateTime(c.created_at) }}
              </span>
            </div>
            {{ c.text }}
          </div>
        </div>
      </transition-group>
      <div class="comments__input">
        <v-textarea
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
    </div>
  </v-dialog>
</template>

<style lang="scss">
.comments {
  $padding: 10px 16px;
  &__items {
    flex: 1;
    // height: 0px; /*here the height is set to 0px*/
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding: $padding;
    & > div {
      margin-bottom: 16px;
    }
  }
  &__input {
    display: flex;
    z-index: 1;
    border-top: 1px solid #e0e0e0;
    padding: $padding;
    // min-height: 65px;
    // height: 65px;
    // max-height: 65px;
    position: sticky;
    bottom: 0;
    background: white;
    .v-btn {
      position: relative;
      top: 4px;
    }
    & textarea {
      max-height: 100px;
      // transition: all linear 0.075s;
      overflow-y: auto;
      scrollbar-width: none; /** ff */
      -ms-overflow-style: none; /** ie */
      font-weight: 400;
      line-height: 18px !important;
      top: 2px;
      position: relative;
      padding: 15px 8px 0 !important;
      &::-webkit-scrollbar {
        width: 0; /** webkit */
      }
    }

    .v-field__outline {
      display: none !important;
    }
  }
  &-wrapper {
    .loaderr {
      position: absolute;
      z-index: 10;
    }
    &--no-scroll {
      &::-webkit-scrollbar {
        display: none;
      }
    }
    &--loaded {
      .new-comment {
        &-enter-active,
        &-leave-active {
          transition: all 100ms linear;
        }
        &-enter-from,
        &-leave-to {
          opacity: 0;
          transform: translateY(20px);
        }
      }
    }
  }
}
.comment {
  display: flex;
  align-items: flex-start;
  font-size: 14px;
  & > div:last-child {
    flex: 1;
  }
  .v-avatar {
    margin-right: 16px;
    margin-top: 1px;
  }
  &__title {
    display: flex;
    align-items: center;
    // margin-bottom: 2px;
    & > *:not(:last-child) {
      margin-right: 6px;
    }
    & > span {
      &:first-child {
        font-weight: bold;
      }
      &:not(:first-child) {
        color: rgb(var(--v-theme-gray));
        font-size: 12px;
        font-weight: normal;
      }
    }
    &:hover {
      .comment-item__controls {
        opacity: 1;
      }
    }
  }
}
.comment-btn {
  &-enter-active {
    transition: all 0.3s linear;
    transform-origin: center center;
  }
  &-leave-active {
    transition: none !important;
  }
  &-enter-from {
    opacity: 0;
    // transform: scale(0.5);
    transform: translateX(-10px);
  }
}
</style>
