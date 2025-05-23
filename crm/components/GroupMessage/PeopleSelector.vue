<script setup lang="ts">
import type { GroupMessageFilters } from '~/components/GroupMessage/Filters.vue'

interface Extra {
  ids: number[]
}

const { event } = defineProps<{
  event?: EventResource
}>()

const route = useRoute()
const router = useRouter()
const loading = ref(false)
const eventId = route.query.event_id

const filters = ref<GroupMessageFilters>({
  mode: 'clients',
  direction: [],
})

const { items, extra, indexPageData } = useIndex<RecepientPerson, Extra>(
  `people-selector`,
  filters,
  {
    staticFilters: {
      event_id: eventId,
    },
  },
)

function getDefaultRecipientIds(): RecipientIds {
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

const selected = ref<RecipientIds>(getDefaultRecipientIds())

const selectedTotal = computed(() => {
  const { clients, teachers } = selected.value
  return clients.length + teachers.length
})

const isSelectedAll = computed(() => selected.value[filters.value.mode].length === extra.value.ids.length)

function select(item: RecepientPerson) {
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

function isSelected(item: RecepientPerson): boolean {
  return isSelectedAll.value || selected.value[filters.value.mode].includes(item.id)
}

if (!event && !eventId) {
  watch(selected, (newVal) => {
    selectedTotal.value === 0
      ? localStorage.removeItem('selected-people')
      : localStorage.setItem('selected-people', JSON.stringify(newVal))
  }, { deep: true })

  nextTick(() => {
    const selectedPeople = localStorage.getItem('selected-people')
    if (selectedPeople) {
      selected.value = JSON.parse(selectedPeople) as RecipientIds
    }
  })
}
</script>

<template>
  <UiIndexPage class="people-selector" :data="indexPageData">
    <template #filters>
      <GroupMessageFilters v-model="filters" />
      <div v-if="!!selectedTotal" class="people-selector__controls">
        <div>
          <v-btn variant="text" @click="clearSelection()">
            отмена
          </v-btn>
        </div>
        <div>
          <v-btn v-if="event" color="primary" :loading="loading" @click="addToEvent()">
            сохранить
            ({{ selectedTotal }})
          </v-btn>
          <v-btn v-else color="primary" @click="$router.push({ name: 'group-message-send' })">
            отправить
            ({{ selectedTotal }})
          </v-btn>
        </div>
      </div>
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
              <UiCheckbox :value="isSelected(item)" />
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
</template>

<style lang="scss">
.people-selector {
  &__filters {
    .filters__inputs {
      width: 100%;
      & > .v-input {
        width: 250px;
        max-width: 250px;
      }
    }
  }
  &__controls {
    display: flex;
    align-items: center;
    width: initial !important;
    flex: 1;
    justify-content: flex-end;
    gap: 10px;
  }
}
</style>
