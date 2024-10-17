<script setup lang="ts">
import { mdiHistory, mdiPhone } from '@mdi/js'
import type { TelegramMessageDialog } from '#build/components'
import { openCallApp } from '~/components/CallApp'

const { items, person, q, verify } = defineProps<{
  items: PhoneListResource[]
  person?: PersonResource
  verify?: boolean
  q?: string
}>()
const telegramMessageDialog = ref<InstanceType<typeof TelegramMessageDialog>>()

// подсвечиваем номер телефона
function highlightPhone(number: string) {
  const text = formatPhone(number)
  const index = number.indexOf(q!)
  if (index === -1) {
    return text
  }
  // 79252727210 => +7 (925) 272-11-22
  const indexMap = [1, 4, 5, 6, 9, 10, 11, 13, 14, 16, 17]
  const start = indexMap[index]
  const end = indexMap[index + q!.length - 1]
  const newQ = text.substr(start, end - start + 1)
  return highlight(text, newQ)
}

// Подсветить результаты поиска. Временно
function highlight(text: string, _q: string) {
  let result = text.trim()
  const qWords = _q.split(' ').filter(Boolean)

  for (const word of qWords) {
    if (!word)
      continue

    const newQ = word.replace(')', '\\)').replace('(', '\\(')
    result = result.replace(
      new RegExp(`${newQ}`, 'gi'),
        `<span class="highlight">${word}</span>`,
    )
  }

  return result
}
</script>

<template>
  <div class="phone-list">
    <div v-for="p in items" :key="p.id">
      <div
        class="phone-list__number"
        :class="{ 'opacity-5': verify && !p.is_verified }"
      >
        <span v-html="q ? highlightPhone(p.number) : formatPhone(p.number)" />
      </div>
      <div class="phone-list__actions">
        <div class="phone-list__comment">
          {{ p.comment ?? 'Неизвестно' }}
        </div>

        <!--        <v-icon -->
        <!--          :icon="mdiSendCircle" -->
        <!--          :disabled="!person" -->
        <!--          @click.stop="telegramMessageDialog?.open(p, person!)" -->
        <!--        /> -->
        <v-icon :icon="mdiHistory" @click.stop="openCallApp(p.number)" />
        <a :href="`tel:${p.number}`" class="d-flex">
          <v-icon :icon="mdiPhone" @click.stop="openCallApp(p.number)" />
        </a>
      </div>
    </div>
  </div>
  <TelegramMessageDialog ref="telegramMessageDialog" />
</template>

<style lang="scss">
.phone-list {
  margin-top: 2px;
  display: inline-block;
  & > div {
    display: flex;
    flex-wrap: nowrap;
    align-items: center;
    position: relative;
    &:hover {
      .phone-list__actions {
        opacity: 1;
        transform: translateX(0) !important;
      }
    }
  }
  &__number {
    cursor: default;
  }
  &__actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 1;
    opacity: 0;
    transition: all ease-in-out 0.2s;
    transform: translateX(6px);
    background: white;
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 120%;
    .v-icon {
      font-size: 18px;
      color: rgb(var(--v-theme-secondary));
      cursor: pointer;
      opacity: 0.6;
      &:hover {
        opacity: 1;
      }
    }
  }
  &__comment {
    cursor: default;
    //color: rgb(var(--v-theme-gray));
    white-space: nowrap;
    text-overflow: ellipsis;
    display: inline-block;
    overflow: hidden;
    font-size: 14px;
    flex: 1;
  }
}
</style>
