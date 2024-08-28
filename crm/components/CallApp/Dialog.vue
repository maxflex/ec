<script setup lang="ts">
import { callAppDialog, player } from '.'

const { activeCalls } = defineProps<{
  activeCalls: CallEvent[]
}>()
const search = ref('')
const { $addSseListener } = useNuxtApp()
const { width } = useDialog('default')
const { items, loading, reloadData } = useIndex<CallListResource>('calls', {
  instantLoad: false,
  scrollContainerSelector: '.call-app-dialog .dialog-body',
})

watch(callAppDialog, (isOpen) => {
  if (isOpen) {
    reloadData()
  }
  else if (player.playing) {
    player.audio!.pause()
    player.playing = false
  }
})

// watch(search, q => reloadData({q})

// Create a debounced version of the reloadData function
const debouncedReloadData = debounce(300, (q: string) => {
  reloadData({ q })
})

watch(search, debouncedReloadData)

$addSseListener('CallSummaryEvent', (call: CallListResource) => {
  if (!callAppDialog.value) {
    return
  }
  items.value.unshift(call)
})
</script>

<template>
  <v-dialog v-model="callAppDialog" :width="width">
    <div class="dialog-wrapper call-app-dialog">
      <!--      <div class="dialog-header"> -->
      <!--        Call app -->
      <!--      </div> -->
      <UiLoader3 :loading="loading" />
      <div class="dialog-body pa-0 ga-0">
        <div class="call-app-search">
          <v-text-field v-model="search" density="comfortable" placeholder="Поиск..." />
        </div>
        <CallAppActiveCallsList v-if="!search" :items="activeCalls" />
        <CallAppCallsList :items="items" />
      </div>
    </div>
  </v-dialog>
</template>

<style lang="scss">
.call-app-search {
  padding: 16px;
}
</style>
