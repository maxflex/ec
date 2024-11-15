<script setup lang="ts">
import type { PhoneDialog } from '#build/components'

const { items, q, request } = defineProps<{
  items: PhoneResource[]
  request?: RequestListResource
  showComment?: boolean
  q?: string
}>()

const phoneDialog = ref<InstanceType<typeof PhoneDialog>>()
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
  const newQ = text.substring(start, end - start + 1)
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
    <div v-for="item in items" :key="item.id" @click="phoneDialog?.open(item)">
      <div
        class="phone-list__number"
        :class="{ 'text-gray': request && !request.is_verified }"
      >
        <span v-html="q ? highlightPhone(item.number) : formatPhone(item.number)" />
      </div>
      <div v-if="showComment" class="phone-list__comment">
        {{ item.comment }}
      </div>
    </div>
  </div>
  <PhoneDialog ref="phoneDialog" />
</template>

<style lang="scss">
.phone-list {
  & > div {
    display: flex;
  }
  &__number {
    cursor: default;
    width: 160px;
  }
  &__comment {
    color: rgb(var(--v-theme-gray));
    overflow: hidden;
    text-overflow: ellipsis;
    display: inline-block;
    white-space: nowrap;
    width: 120px;
  }
}
</style>
