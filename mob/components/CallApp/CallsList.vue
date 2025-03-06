<script setup lang="ts">
import { mdiChevronDown, mdiPhoneIncoming, mdiPhoneMissed, mdiPhoneOutgoing } from '@mdi/js'
import { differenceInWeeks, format, isSameDay, parse } from 'date-fns'
import { ru } from 'date-fns/locale'
import { Vue3SlideUpDown } from 'vue3-slide-up-down'

const { items } = defineProps<{
  items: CallListResource[]
}>()
const emit = defineEmits<{
  deleted: [call: CallListResource]
}>()
const dateFormat = 'yyyy-MM-dd HH:mm:ss'
const expanded = ref<{ [key: string]: boolean }>({})

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

function onDelete(e: MouseEvent, call: CallListResource) {
  e.stopPropagation()
  emit('deleted', call)
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
      class="calls-list__item cursor-pointer"
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
          <CallAppDuration :item="call" />
        </div>
        <div class="calls-list__date">
          {{ formatCallDate(call) }}
          <v-icon :icon="mdiChevronDown" />
        </div>
      </div>
      <CallAppAon :item="call.aon" />
      <Vue3SlideUpDown :model-value="expanded[call.id]" :duration="200">
        <div v-if="call.aon?.comment">
          {{ call.aon.comment }}
        </div>
        <div v-if="call.user">
          {{ call.type === 'incoming' ? 'Принял' : 'Позвонил' }}
          {{ formatName(call.user) }}
        </div>
        <div v-if="call.is_missed && !call.is_missed_callback">
          <a class="text-error" @click="e => onDelete(e, call)">
            Удалить
          </a>
        </div>
        <CallAppPlayer v-if="call.has_recording" :item="call" />
      </Vue3SlideUpDown>
    </div>
  </div>
</template>
