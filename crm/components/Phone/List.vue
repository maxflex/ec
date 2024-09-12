<script setup lang="ts">
import { mdiHistory, mdiSend } from '@mdi/js'
import type { TelegramMessageDialog } from '#build/components'
import { openCallApp } from '~/components/CallApp'

const { items, person, q } = defineProps<{
  items: PhoneListResource[]
  person: PersonResource
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
      <div class="phone-list__number">
        <a
          :href="`tel:${p.number}`"
          @click.stop
          v-html="q ? highlightPhone(p.number) : formatPhone(p.number)"
        />
      </div>
      <div class="phone-list__actions">
        <v-icon :icon="mdiHistory" @click.stop="openCallApp(p.number)" />
        <v-icon
          :icon="mdiSend"
          :class="{ 'opacity-disabled': !p.telegram_id }"
          @click.stop="telegramMessageDialog?.open(p, person)"
        />
      </div>
    </div>
  </div>
  <TelegramMessageDialog ref="telegramMessageDialog" />
</template>

<style lang="scss">
.phone-list {
  margin-top: 2px;
  width: 250px;
  display: inline-block;
  & > div {
    display: flex;
    flex-wrap: nowrap;
    align-items: center;
    &:hover {
      .phone-list__actions {
        opacity: 1;
      }
    }
  }
  &__number {
    min-width: 170px;
  }
  &__actions {
    display: flex;
    gap: 8px;
    flex: 1;
    opacity: 0;
    transition: opacity cubic-bezier(0.4, 0, 0.2, 1) 0.2s;
    .v-icon {
      // top: -2px;
      left: 1px;
      font-size: 18px;
      color: rgb(var(--v-theme-secondary));
      cursor: pointer;
    }
  }
}
</style>
