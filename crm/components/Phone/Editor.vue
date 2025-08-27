<script setup lang="ts">
const { editTelegram, disabled } = defineProps<{
  editTelegram?: boolean
  disabled?: boolean
}>()
const model = defineModel<PhoneResource[]>({ default: () => [] })
const phoneMask = { mask: '+7 (###) ###-##-##' }
function addPhone() {
  model.value.push({
    id: newId(),
    number: '',
    comment: '',
    telegram_id: null,
    entity_type: EntityTypeValue.request,
    entity_id: -1,
    is_telegram_disabled: false,
  })
}

function removePhone(p: PhoneResource) {
  model.value.splice(
    model.value.findIndex(({ id }) => id === p.id),
    1,
  )
}
</script>

<template>
  <div
    v-for="p in model"
    :key="p.id"
    class="phone-editor double-input"
  >
    <div>
      <v-text-field
        v-model="p.number"
        v-maska="phoneMask"
        :label="p.telegram_id ? 'Телефон / Телеграм' : 'Телефон'"
        :disabled="disabled || p.id > 0"
        :class="{
          'phone-editor--has-telegram': !!p.telegram_id,
        }"
      />
      <div v-if="!disabled" class="input-actions">
        <span
          v-if="editTelegram"
          :class="p.is_telegram_disabled ? 'text-error' : 'text-gray'"
          @click="p.is_telegram_disabled = !p.is_telegram_disabled"
        >
          без telegram
        </span>
        <span class="phone-editor__remove" @click="removePhone(p)">
          удалить
        </span>
      </div>
    </div>
    <v-text-field
      v-model="p.comment"
      label="Комментарий"
      :disabled="disabled"
    />
  </div>
  <div v-if="!disabled" style="position: relative; top: -10px">
    <a
      class="cursor-pointer"
      @click="addPhone()"
    >
      добавить номер
    </a>
  </div>
</template>

<style lang="scss">
.phone-editor {
  & > div {
    &:first-child {
      position: relative;
    }
  }
  &__actions {
    position: absolute;
    right: 10px;
    top: 19px;
    display: flex;
    gap: 10px;

    .v-icon {
      font-size: 20px !important;

      &:first-child {
        color: #bfbfbf;
        cursor: default !important;
      }

      &:nth-child(2) {
        opacity: 0.25;
        &:hover {
          opacity: 1;
          color: rgb(var(--v-theme-gray));
        }
      }

      &:last-child {
        z-index: 1;
        cursor: pointer;
        opacity: 0.25;
        &:hover {
          color: rgb(var(--v-theme-error));
          opacity: 1;
        }
      }
    }
  }
  &__remove {
    color: rgb(var(--v-theme-gray));
    &:hover {
      color: rgb(var(--v-theme-error));
    }
  }
  &--has-telegram {
    .v-field__outline__notch label {
      opacity: 1;
      color: rgb(var(--v-theme-secondary)) !important;
    }
  }
}
</style>
