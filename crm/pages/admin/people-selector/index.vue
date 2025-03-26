<script setup lang="ts">
import type { PeopleSelectorFilters } from '~/components/PeopleSelector/Filters.vue'

const filters = ref<PeopleSelectorFilters>({
  mode: 'clients',
  direction: [],
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
      <PeopleSelectorFilters v-model="filters" />
    </template>
    <v-table hover class="people-selector-table">
      <tbody>
        <tr @click="selectAll()">
          <td colspan="1000">
            <div>
              <UiCheckbox :value="isSelectedAll" />
              <span> всего: {{ extra.ids.length }} </span>
            </div>
          </td>
        </tr>
        <tr v-for="item in items" :key="item.id" @click="select(item)">
          <td style="width: 500px">
            <div>
              <UiCheckbox :value="isSelectedAll || selected[filters.mode].some(id => id === item.id)" />
              <UiPerson :item="item" teacher-format="full" />
            </div>
          </td>
          <td>
            <template v-if="item.directions">
              {{ item.directions.map(e => DirectionLabel[e]).join(', ') }}
            </template>
          </td>
        </tr>
      </tbody>
    </v-table>
  </UiIndexPage>
  <UiBottomBar :model-value="!!selectedTotal">
    <div>
      <v-btn icon="$close" :size="30" variant="text" @click="clearSelection()" />
      выбрано: {{ selectedTotal }}
    </div>
    <div>
      <v-btn variant="text" @click="$router.push({ name: 'people-selector-send' })">
        отправить сообщение
      </v-btn>
    </div>
  </UiBottomBar>
</template>
