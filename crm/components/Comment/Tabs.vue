<script setup lang="ts">
/**
 * Работает только для комментариев клиента
 */

import type { CommentTab } from '.'
import { commentTabs } from '.'

const { tabCounts } = defineProps<{
  tabCounts?: Record<CommentTab, number>
}>()
const { tabs } = useTabs(commentTabs)
const model = defineModel<CommentTab>({ required: true })
</script>

<template>
  <UiTabs v-model="model" :items="tabs" :counts="tabCounts"></UiTabs>
</template>
