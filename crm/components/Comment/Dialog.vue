<script setup lang="ts">
import { mdiSendVariant } from '@mdi/js'

const { dialog, width } = useDialog('default')
const { entityId, entityType } = defineProps<{
  entityId: number
  entityType: EntityString
}>()
const emit = defineEmits<{ (e: 'created'): void }>()
const comments = ref<CommentResource[]>([])
const input = ref()
const wrapper = ref()
const text = ref('')

function scrollBottom() {
  nextTick(() => {
    console.log(wrapper.value)
    wrapper.value.scrollTo({ top: 99999 })
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
      entity_type: EntityType[entityType],
    },
  })
  if (data.value) {
    comments.value.push(data.value)
    text.value = ''
    scrollBottom()
    emit('created')
  }
}

async function loadData() {
  const { data } = await useHttp<ApiResponse<CommentResource[]>>('comments', {
    params: {
      entity_id: entityId,
      entity_type: EntityType[entityType],
    },
  })
  if (data.value) {
    comments.value = data.value.data
    scrollBottom()
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
      id="wrapperr"
      ref="wrapper"
      class="dialog-wrapper"
    >
      <div class="comments__items">
        <div
          v-for="c in comments"
          :key="c.id"
          class="comment"
        >
          <UserAvatar
            :user="c.user"
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
      </div>
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
        <v-slide-x-transition>
          <v-btn
            v-if="text.length > 0"
            :icon="mdiSendVariant"
            height="48"
            width="48"
            variant="text"
            color="secondary"
            @click="send()"
          />
        </v-slide-x-transition>
      </div>
    </div>
  </v-dialog>
</template>

<style lang="scss">
.comments {
  $padding: 10px 16px;
  &__items {
    flex: 1 1 auto;
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
    min-height: 65px;
    position: relative;
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
</style>
