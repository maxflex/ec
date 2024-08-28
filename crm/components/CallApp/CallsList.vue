<script setup lang="ts">
import { mdiChevronDown, mdiPhoneIncoming, mdiPhoneMissed, mdiPhoneOutgoing } from '@mdi/js'
import { differenceInSeconds, differenceInWeeks, format, isSameDay, parse } from 'date-fns'
import { Vue3SlideUpDown } from 'vue3-slide-up-down'
import { ru } from 'date-fns/locale'

const { items } = defineProps<{
  items: CallListResource[]
}>()
const dateFormat = 'yyyy-MM-dd HH:mm:ss'
const expanded = ref<{ [key: string]: boolean }>({})
function getCallDuration(call: CallListResource) {
  const answeredAt = parse(call.answered_at!, dateFormat, new Date())
  const finishedAt = parse(call.finished_at, dateFormat, new Date())
  const seconds = differenceInSeconds(finishedAt, answeredAt)
  return format(new Date(seconds * 1000), 'mm:ss')
}

function formatCallDate(call: CallListResource) {
  const date = parse(call.created_at, dateFormat, new Date())

  if (isSameDay(date, new Date())) {
    return format(date, 'сегодня в HH:mm')
  }

  if (differenceInWeeks(new Date(), date) < 1) {
    return format(date, 'eeeeee в HH:mm', { locale: ru })
  }

  return format(date, 'dd.MM.yy')
}

function onClick(call: CallListResource) {
  if (call.id in expanded.value) {
    delete expanded.value[call.id]
  }
  else {
    expanded.value[call.id] = true
  }
}
</script>

<template>
  <div class="calls-list">
    <div
      v-for="call in items"
      :key="call.id"
      :class="{
        'calls-list__item--expanded': expanded[call.id],
      }"
      class="calls-list__item"
      @click="onClick(call)"
    >
      <div>
        <div class="calls-list__number">
          <v-icon
            v-if="call.is_missed"
            :icon="mdiPhoneMissed"
            :color="call.is_missed_callback ? 'orange' : 'error'"
          />
          <v-icon v-else-if="call.type === 'incoming'" color="gray" :icon="mdiPhoneIncoming" />
          <v-icon v-else color="gray" :icon="mdiPhoneOutgoing" />
          <span>
            {{ formatPhone(call.number) }}
          </span>
        </div>
        <div v-if="call.answered_at">
          {{ getCallDuration(call) }}
        </div>
        <div class="calls-list__date">
          {{ formatCallDate(call) }}
          <v-icon :icon="mdiChevronDown" />
        </div>
      </div>
      <CallAppPersonLink :phone="call.phone" />
      <Vue3SlideUpDown :model-value="expanded[call.id]" :duration="200">
        <div v-if="call.phone?.comment">
          {{ call.phone.comment }}
        </div>
        <div v-if="call.user">
          <template v-if="call.type === 'incoming'">
            Позвонил
          </template>
          <template v-else>
            Принял
          </template>
          {{ formatName(call.user) }}
        </div>
        <CallAppPlayer v-if="call.has_recording" :item="call" />
      </Vue3SlideUpDown>
    </div>
  </div>
</template>

<style lang="scss">
.calls-list {
  //font-size: 14px;
  &__item,
  .slide-up-down__container {
    & > div {
      margin-bottom: 10px;
    }
  }
  &__item {
    border-bottom: 1px solid rgb(var(--v-theme-border));
    padding: 16px 16px 6px;
    color: #9e9e9e;
    cursor: pointer;
    transition: background 0.28s cubic-bezier(0.4, 0, 0.2, 1);
    user-select: none;
    &:hover {
      background: #f5f5f5;
    }
    .v-icon {
      transition: transform linear 0.2s;
    }
    & > div {
      &:first-child {
        display: flex;
        align-items: center;
        justify-content: space-between;
      }
    }
    &--expanded {
      .calls-list__date {
        .v-icon {
          transform: rotate(-180deg);
        }
      }
    }
  }
  &__number {
    display: flex;
    //align-items: center;
    gap: 10px;
    width: 230px;
    color: black;
    span {
      font-weight: 500;
    }
  }
  &__date {
    align-items: center;
    display: flex;
    opacity: 0.5;
  }
}
</style>
