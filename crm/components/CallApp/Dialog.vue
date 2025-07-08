<script setup lang="ts">
import type { CallEvent, CallListResource } from '.'
import { callAppDialog } from '.'

const { activeCalls } = defineProps<{
  activeCalls: CallEvent[]
}>()

const { $addSseListener } = useNuxtApp()
const { width, transition } = useDialog('default')
const historyItem = ref<PhoneResource>()
const { items, loading, reloadData } = useIndex<CallListResource>(
  `calls`,
  ref({}),
  {
    instantLoad: false,
    saveFilters: false,
    scrollContainerSelector: '.call-app-dialog .dialog-body',
  },
)

watch(callAppDialog, (isOpen) => {
  if (isOpen) {
    reloadData()
  }

  // copied from useDialog.ts
  const el = document.documentElement.querySelector(
    '.v-dialog.v-overlay--active > .dialog',
  )
  if (el === null) {
    transition.value = 'dialog-fade-transition'
  }
  else {
    transition.value = 'dialog-second-transition'
    // @ts-expect-error
    el.style.right = isOpen ? `${width * 0.5}px` : null
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
  <v-dialog
    v-model="callAppDialog"
    :width="width"
    :transition="transition"
    class="call-app"
    :class="{ 'call-app__phone-dialog-active': historyItem !== undefined }"
  >
    <!-- <v-slide-x-reverse-transition> -->
    <transition name="call-slide-transition">
      <PhoneDialogContent
        v-if="historyItem"
        :item="historyItem"
        back-btn
        class="call-app__phone-dialog"
        @back="historyItem = undefined"
      />
    </transition>

    <!-- </v-slide-x-reverse-transition> -->
    <div class="dialog-wrapper call-app-dialog">
      <div class="dialog-body pa-0 ga-0">
        <div>
          <CallAppActiveCallsList :items="activeCalls" />
          <CallAppCallsList v-if="items.length" :items="items" @history="e => (historyItem = e)" />
          <UiNoData v-else-if="!loading && activeCalls.length === 0" />
        </div>
      </div>
    </div>
  </v-dialog>
</template>

<style lang="scss">
.call-slide-transition {
  &-enter-active,
  &-leave-active {
    position: absolute;
    width: 100%;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 10;
    box-shadow: 0 0 3px 3px rgba(black, 0.1);
  }

  &-leave-from,
  &-enter-to {
    transform: translateX(0%);
  }

  &-enter-from,
  &-leave-to {
    transform: translateX(100%);
  }
}
</style>
