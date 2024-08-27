<script setup lang="ts">
import { callAppDialog, player } from '.'

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
      <div class="dialog-body pa-0">
        <CallAppCallsList :items="items" />
      </div>
    </div>
  </v-dialog>
</template>

<style scoped lang="scss">

</style>
