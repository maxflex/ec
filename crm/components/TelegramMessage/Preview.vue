<script setup lang="ts">
const { text } = defineProps<{ text: string }>()
</script>

<template>
  <v-slide-x-transition>
    <div v-if="text" class="telegram-message-preview">
      <div class="text-gray mb-2">
        Так будет выглядеть сообщение в Telegram
      </div>
      <div class="message">
        <div>
          <div class="tg-message__avatar">
            EC
          </div>
        </div>
        <div>
          <div class="font-weight-bold">
            ЕГЭ-Центр
          </div>
          <div class="telegram-message-preview-text" v-html="text"></div>
        </div>
      </div>
    </div>
  </v-slide-x-transition>
</template>

<style lang="scss">
.telegram-message-preview {
  position: absolute;
  left: 530px;
  top: 0;
  width: 468px;
  cursor: default;
  font-size: 14px;

  .message {
    background: rgb(var(--v-theme-bg));
    border-radius: 8px;
    padding: 10px 20px;
    display: flex;
    align-items: flex-start;
  }

  &-text {
    white-space: break-spaces;
    word-break: break-word;
    pointer-events: none;
  }
}
</style>
