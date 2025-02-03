<script setup lang="ts">
import { mdiEmailOffOutline, mdiEmailOutline } from '@mdi/js'

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
        v-maska:[phoneMask]
        label="Телефон"
      />
      <div class="phone-editor__actions">
        <v-icon
          :class="{
            'phone-editor__telegram--has-telegram': !!p.telegram_id,
          }"
          icon="$send"
        />
        <v-icon
          :icon="p.is_telegram_disabled ? mdiEmailOffOutline : mdiEmailOutline"
          :class="{
            'phone-editor__telegram--disabled': p.is_telegram_disabled,
          }"
          @click="p.is_telegram_disabled = !p.is_telegram_disabled"
        />
        <v-icon
          icon="$close"
          @click="removePhone(p)"
        />
      </div>
    </div>
    <v-text-field
      v-model="p.comment"
      label="Комментарий"
    />
  </div>
  <div style="position: relative; top: -10px">
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

  &__telegram {
    &--has-telegram {
      color: rgb(var(--v-theme-secondary)) !important;
    }
    &--disabled {
      color: rgb(var(--v-theme-error)) !important;
    }
  }
}
</style>
