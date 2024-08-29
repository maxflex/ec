<script setup lang="ts">
import { callAppDialog, filters, missedCount, player } from '.'
import { CallAppStatusFilterLabel } from '~/utils/labels'

const { activeCalls } = defineProps<{
  activeCalls: CallEvent[]
}>()

const { $addSseListener } = useNuxtApp()
const { width } = useDialog('default')
const { items, loading, reloadData } = useIndex<CallListResource>('calls', {
  instantLoad: false,
  scrollContainerSelector: '.call-app-dialog .dialog-body',
})

// Create a debounced version of the reloadData function
const debouncedReloadData = debounce(300, reloadWithFilters)

// Watcher variable
let stopFilterWatcher: (() => void) | null = null

function reloadWithFilters() {
  console.log(reloadWithFilters.name)
  reloadData(filters.value)
}

watch(callAppDialog, (isOpen) => {
  if (isOpen) {
    reloadWithFilters()
    stopFilterWatcher = watch(filters, debouncedReloadData, { deep: true })
  }
  else {
    // stop playing audio
    if (player.playing) {
      player.audio!.pause()
      player.playing = false
    }

    // clear items
    setTimeout(() => (items.value = []), 300)

    stopFilterWatcher && stopFilterWatcher()
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
          <v-text-field v-model="filters.q" density="comfortable" placeholder="Поиск...">
            <template #append>
              <UiDropdown
                v-model="filters.status"
                :items="selectItems(CallAppStatusFilterLabel)"
              />
            </template>
          </v-text-field>
        </div>
        <CallAppActiveCallsList
          v-if="!filters.q && ['all', 'active'].includes(filters.status)"
          :items="activeCalls"
        />
        <CallAppCallsList :items="items" @deleted="onDeleted" />
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
