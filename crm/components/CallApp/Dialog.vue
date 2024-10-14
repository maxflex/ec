<script setup lang="ts">
import { callAppDialog, filters, missedCount, player } from '.'
import { CallAppStatusFilterLabel } from '~/utils/labels'

const { activeCalls } = defineProps<{
  activeCalls: CallEvent[]
}>()

const q = ref('')

const { $addSseListener } = useNuxtApp()
const { width } = useDialog('default')
const { items, loading } = useIndex<CallListResource>('calls', filters, {
  instantLoad: false,
  scrollContainerSelector: '.call-app-dialog .dialog-body',
})

const showActiveCalls = computed(() => !filters.value.q && ['all', 'active'].includes(filters.value.status))

const debounceSearch = debounce(300, () => (filters.value.q = q.value))

watch(q, debounceSearch)

watch(callAppDialog, (isOpen) => {
  if (isOpen) {
    q.value = filters.value.q
  }
  else {
    // stop playing audio
    if (player.playing) {
      player.audio!.pause()
      player.playing = false
    }

    // clear items
    setTimeout(() => (items.value = []), 300)
  }
})

async function onDeleted(call: CallListResource) {
  if (confirm('Удалить пропущенный звонок?')) {
    const index = items.value.findIndex(e => e.id === call.id)
    items.value.splice(index, 1)
    await useHttp(`calls/${call.id}`, {
      method: 'delete',
    })
    missedCount.value--
  }
}

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
      <UiLoader3 :loading="loading" />
      <div class="dialog-body pa-0 ga-0">
        <div class="call-app-search">
          <v-text-field v-model="q" density="comfortable" placeholder="Поиск...">
            <template #append>
              <UiDropdown
                v-model="filters.status"
                :items="selectItems(CallAppStatusFilterLabel)"
              />
            </template>
          </v-text-field>
        </div>
        <div>
          <CallAppActiveCallsList v-if="showActiveCalls" :items="activeCalls" />
          <CallAppCallsList v-if="items.length" :items="items" @deleted="onDeleted" />
          <UiNoData v-else-if="!loading && (!showActiveCalls || activeCalls.length === 0)" />
        </div>
      </div>
    </div>
  </v-dialog>
</template>

<style lang="scss">
.call-app-search {
  padding: 16px;
  .v-input {
    input {
      width: 75%;
      flex: none !important;
    }
    &__append {
      position: relative;
      margin-inline-start: 0 !important;
    }
  }
  .ui-dropdown {
    position: absolute;
    white-space: nowrap;
    right: 10px;
    color: #9e9e9e !important;
    &:before {
      content: '';
      position: absolute;
      height: 100%;
      width: 1px;
      background: #cecece;
      top: 0;
      left: -10px;
      opacity: 0.5;
    }
  }
}
</style>
