<script setup lang="ts">
import type { EventAddToDialog, TelegramListDialog } from '#components'

const telegramListDialog = shallowRef<InstanceType<typeof TelegramListDialog>>()
const eventAddToDialog = shallowRef<InstanceType<typeof EventAddToDialog>>()
const filters = ref<PeopleSelectorFilters>({
  mode: 'clients',
  year: 2023,
  program: [],
  statuses: [],
  subjects: [],
})

const { items, extra, indexPageData } = useIndex<
    PersonResource,
    PeopleSelectorFilters,
    PeopleSelectorExtra
>(
    `people-selector`,
    filters,
)

const selected = ref<SelectedPeople>({
  clients: [],
  teachers: [],
})

const selectedTotal = computed(() => {
  const { clients, teachers } = selected.value
  return clients.length + teachers.length
})

const isSelectedAll = computed(() => selected.value[filters.value.mode].length === extra.value.ids.length)

function select(item: PersonResource) {
  const index = selected.value[filters.value.mode].findIndex(id => id === item.id)
  index === -1
    ? selected.value[filters.value.mode].push(item.id)
    : selected.value[filters.value.mode].splice(index, 1)
}

function selectAll() {
  selected.value[filters.value.mode] = isSelectedAll.value ? [] : [...extra.value.ids]
}

function clearSelection() {
  selected.value = {
    clients: [],
    teachers: [],
  }
}

watch(selected, (newVal) => {
  selectedTotal.value === 0
    ? localStorage.removeItem('selected-people')
    : localStorage.setItem('selected-people', JSON.stringify(newVal))
}, { deep: true })

nextTick(() => {
  const selectedPeople = localStorage.getItem('selected-people')
  if (selectedPeople) {
    selected.value = JSON.parse(selectedPeople) as SelectedPeople
  }
})
</script>

<template>
  <UiIndexPage class="people-selector" :data="indexPageData">
    <template #filters>
      <PeopleSelectorFilters v-model="filters" :group-ids="extra.group_ids || []" />
    </template>
    <v-table hover class="people-selector-table">
      <tbody>
        <tr @click="selectAll()">
          <td colspan="100">
            <UiCheckbox :value="isSelectedAll" />
            <span> всего: {{ extra.ids.length }} </span>
          </td>
        </tr>
        <tr v-for="item in items" :key="item.id" @click="select(item)">
          <td>
            <UiCheckbox :value="isSelectedAll || selected[filters.mode].some(id => id === item.id)" />
            <UiPerson :item="item" teacher-format="full" />
          </td>
        </tr>
      </tbody>
    </v-table>
  </UiIndexPage>
  <v-slide-y-reverse-transition>
    <div v-if="selectedTotal" class="bottom-bar">
      <div>
        <v-btn icon="$close" :size="30" variant="text" @click="clearSelection()" />
        выбрано: {{ selectedTotal }}
      </div>
      <div>
        <v-btn variant="text" @click="eventAddToDialog?.open(selected, filters.year)">
          добавить к событию
        </v-btn>
        <v-btn color="secondary" :to="{ name: 'people-selector-send' }">
          отправить
          <template #append>
            <v-icon icon="$next" />
          </template>
        </v-btn>
      </div>
    </div>
  </v-slide-y-reverse-transition>
  <TelegramListDialog ref="telegramListDialog" />
  <EventAddToDialog ref="eventAddToDialog" />
</template>

<style lang="scss">
.people-selector {
  &__filters {
    --height: auto !important;
    padding-bottom: 24px !important;
    .filters__inputs {
      width: 100%;
      & > div {
        //flex: 1;
        //width: auto !important;
        width: 227px !important;
      }
    }
  }
}
</style>
