<script setup lang="ts">
import type { PeopleSelectorFilters } from '~/components/PeopleSelector/Filters.vue'

const { event } = defineProps<{
  event?: EventResource
}>()

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

const router = useRouter()
const loading = ref(false)

function getDefaultSelectedPeople(): SelectedPeople {
  if (event) {
    return {
      clients: event.participants!.clients.map(e => e.entity.id),
      teachers: event.participants!.teachers.map(e => e.entity.id),
    }
  }
  return {
    clients: [],
    teachers: [],
  }
}

const selected = ref<SelectedPeople>(getDefaultSelectedPeople())

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

async function addToEvent() {
  if (!event) {
    return
  }

  loading.value = true
  await useHttp(
    `event-participants`,
    {
      method: 'post',
      body: {
        id: event.id,
        selected: selected.value,
      },
    },
  )
  await router.push({ name: 'events-id', params: { id: event.id } })
}
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
      <v-btn v-if="event" variant="text" :loading="loading" @click="addToEvent()">
        сохранить
      </v-btn>
    </div>
  </UiBottomBar>
</template>
