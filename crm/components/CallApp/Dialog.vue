<script setup lang="ts">
import { useDebounceFn } from '@vueuse/core'
import { callAppDialog, filters, player } from '.'

const { activeCalls } = defineProps<{
  activeCalls: CallEvent[]
}>()

const q = ref('')

const { $addSseListener } = useNuxtApp()
const { width, transition } = useDialog('default')
const { items, loading } = useIndex<CallListResource>(
  `calls`,
  filters,
  {
    instantLoad: false,
    saveFilters: false,
    scrollContainerSelector: '.call-app-dialog .dialog-body',
  },
)

const showActiveCalls = computed(() => !filters.value.q && ['all', 'active'].includes(filters.value.status))

const debounceSearch = useDebounceFn(() => (filters.value.q = q.value), 300)

watch(q, debounceSearch)

watch(callAppDialog, (isOpen) => {
  if (isOpen) {
    q.value = filters.value.q
  }
  else {
    // clear items
    setTimeout(() => (items.value = []), 300)
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
  <v-dialog v-model="callAppDialog" :width="width" :transition="transition">
    <div class="dialog-wrapper call-app-dialog">
      <div class="dialog-body pa-0 ga-0">
        <div>
          <CallAppActiveCallsList v-if="showActiveCalls" :items="activeCalls" />
          <CallAppCallsList v-if="items.length" :items="items" />
          <UiNoData v-else-if="!loading && (!showActiveCalls || activeCalls.length === 0)" />
        </div>
      </div>
    </div>
  </v-dialog>
</template>
