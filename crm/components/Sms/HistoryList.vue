<script setup lang="ts">
import type { SmsListResource } from '.'
import { mdiAlertCircleOutline, mdiCheckAll } from '@mdi/js'
import { format } from 'date-fns'

const { items } = defineProps<{
  items: SmsListResource[]
}>()
</script>

<template>
  <div class="sms-message__wrapper">
    <div
      v-for="item in items"
      :key="item.id"
      class="sms-message__item"
    >
      <div class="sms-message__avatar">
        EC
      </div>
      <div>
        <div class="sms-message__title">
          <span>
            ЕГЭ-Центр
          </span>
          <span class="d-flex align-center ga-1">
            <v-tooltip>
              <template #activator="{ props }">
                <v-icon v-if="item.status === 1" color="success" :icon="mdiCheckAll" :size="14" v-bind="props" />
                <v-icon v-else color="error" :icon="mdiAlertCircleOutline" :size="14" v-bind="props" />
              </template>
              {{ item.status_name.toLowerCase() }}
            </v-tooltip>
            <span v-if="item.created_at">
              {{ format(item.created_at, 'dd.MM.yy в HH:mm') }}
            </span>
          </span>
        </div>
        <div class="sms-message__text" v-html="item.message" />
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.sms-message {
  $padding: 10px 16px;
  &__wrapper {
    flex: 1;
    // height: 0px; /*here the height is set to 0px*/
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    padding: $padding;
    & > div {
      margin-bottom: 24px;
    }
  }
  &__title {
    display: flex;
    align-items: center;
    justify-content: space-between;
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
    .v-icon--clickable {
      opacity: 0;
      transition: opacity ease-in-out 0.2s;
    }
    &:hover {
      .v-icon--clickable {
        opacity: 1;
      }
    }
  }
  &__item {
    display: flex;
    align-items: flex-start;
    font-size: 14px;
    & > div:last-child {
      flex: 1;
    }
  }
  &__avatar {
    $size: 46px;
    height: $size;
    width: $size;
    margin-right: 16px;
    margin-top: 1px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgb(var(--v-theme-deepOrange));
    //background: white;
    border-radius: 50%;
    font-size: 20px;
    font-weight: 500;
    color: white;
    //border: 1px solid rgb(var(--v-theme-border));
    //img {
    //  width: 32px;
    //}
  }
  &__text {
    white-space: pre-line;
  }
}
</style>
