<script setup lang="ts">
import { mdiDownload } from '@mdi/js'

const { item } = defineProps<{
  item: CallListResource
}>()

const loading = ref(false)

async function downloadAudio(e: MouseEvent) {
  loading.value = true
  e.stopPropagation()
  const audio = await getAudio('download')
  const link = document.createElement('a')
  link.href = audio
  link.click()
  setTimeout(() => (loading.value = false), 300)
}

async function getAudio(action: 'play' | 'download') {
  const { data } = await useHttp(
    `calls/recording/${action}/${item.id}`,
  )
  return data.value as string
}
</script>

<template>
  <v-btn class="call-app__download" density="compact" :icon="mdiDownload" :loading="loading" @click="downloadAudio" />
</template>

<style lang="scss">
.call-app {
  &__download {
    position: absolute !important;
    right: -10px;
    top: -4px;
  }
}
</style>
