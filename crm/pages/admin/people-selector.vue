<script setup lang="ts">
import type { EventAddToDialog, TelegramListDialog } from '#components'

const telegramListDialog = shallowRef<InstanceType<typeof TelegramListDialog>>()
const eventAddToDialog = shallowRef<InstanceType<typeof EventAddToDialog>>()
const filters = ref<PeopleSelectorFilters>({
  mode: 'clients',
  year: 2023,
  programs: [],
  statuses: [],
  subjects: [],
})

const { items, extra, indexPageData } = useIndex<
    PersonListResource,
    PeopleSelectorFilters,
    PeopleSelectorExtra
>(
    `people-selector`,
    filters,
)

const selected = reactive<SelectedPeople>({
  clients: [],
  teachers: [],
})

const selectedTotal = computed(() => selected.clients.length + selected.teachers.length)

const isSelectedAll = computed(() => selected[filters.value.mode].length === extra.value.ids.length)

function select(item: PersonResource) {
  const index = selected[filters.value.mode].findIndex(id => id === item.id)
  index === -1
    ? selected[filters.value.mode].push(item.id)
    : selected[filters.value.mode].splice(index, 1)
}

function selectAll() {
  selected[filters.value.mode] = isSelectedAll.value ? [] : [...extra.value.ids]
}

function clearSelection() {
  selected.clients = []
  selected.teachers = []
}
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
            <v-icon
              v-if="isSelectedAll"
              color="secondary"
              icon="$checkboxOn"
            />
            <v-icon
              v-else
              icon="$checkboxOff"
              class="opacity-6"
            />
            <span>
              всего: {{ extra.ids.length }}
            </span>
          </td>
        </tr>
        <tr v-for="item in items" :key="item.id" @click="select(item)">
          <td>
            <v-icon
              v-if="isSelectedAll || selected[filters.mode].some(id => id === item.id)"
              color="secondary"
              icon="$checkboxOn"
            />
            <v-icon
              v-else
              icon="$checkboxOff"
              class="opacity-6"
            />
            <UiPersonLink :item="item" :type="filters.mode" />
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
        <!--        <v-btn variant="text" icon="$close" :size="44" @click="clearSelection()" /> -->
        <v-btn variant="text" @click="eventAddToDialog?.open(selected, filters.year)">
          добавить к событию
        </v-btn>
        <v-btn color="secondary" @click="() => telegramListDialog?.open(selected)">
          отправить
          <template #append>
            <v-icon icon="$send" />
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
