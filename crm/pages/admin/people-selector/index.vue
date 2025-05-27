<script setup lang="ts">
const route = useRoute()
const router = useRouter()
const loading = ref(true)

// отправка сообщения по событию
const sendEventId = route.query.event_id

// редактирование участников события
const participantsEventId = route.query.participants

const saving = ref(false)
const mode = ref<Recepient>('clients')
const people = ref<Recipients>({
  clients: [],
  teachers: [],
})
const clientLiveFilters = ref<{
  q: string
  directions: Direction[]
}>({
  q: '',
  directions: [],
})

const selected = ref<RecipientIds>({
  clients: [],
  teachers: [],
})

const noData = computed(() => !Object.values(people.value).some(e => !!e.length))

const isSelectedAll = computed(() => selected.value[mode.value].length === people.value[mode.value].length)

const selectedTotal = computed(() => {
  const { clients, teachers } = selected.value
  return clients.length + teachers.length
})

async function loadData() {
  loading.value = true
  const { data } = await useHttp<Recipients>(
    `people-selector`,
    {
      params: {
        event_id: sendEventId,
      },
    },
  )
  people.value = data.value!
  loading.value = false
}

function select(item: RecepientPerson) {
  const index = selected.value[mode.value].findIndex(id => id === item.id)
  index === -1
    ? selected.value[mode.value].push(item.id)
    : selected.value[mode.value].splice(index, 1)
}

function selectAll() {
  selected.value[mode.value] = isSelectedAll.value ? [] : people.value[mode.value].map(e => e.id)
}

function clearSelection() {
  selected.value = {
    clients: [],
    teachers: [],
  }
}

async function saveParticipants() {
  if (!participantsEventId) {
    return
  }

  saving.value = true
  await useHttp(
    `event-participants`,
    {
      method: 'post',
      body: {
        id: participantsEventId,
        selected: selected.value,
      },
    },
  )
  await router.push({ name: 'events-id', params: { id: participantsEventId as string } })
}

function isSelected(item: RecepientPerson): boolean {
  return isSelectedAll.value || selected.value[mode.value].includes(item.id)
}

const itemsFiltered = computed<RecepientPerson[]>(() => {
  if (mode.value === 'teachers') {
    return people.value.teachers
  }

  const { q, directions } = clientLiveFilters.value
  const query = q.trim().toLowerCase()

  return people.value.clients.filter((c) => {
    const nameMatch = query
      ? [c.last_name, c.first_name]
          .join(' ')
          .toLowerCase()
          .includes(query)
      : true

    const directionsMatch = directions.length
      ? directions.some(d => c.directions!.includes(d))
      : true

    return nameMatch && directionsMatch
  })
})

watch(mode, () => smoothScroll('main', 'top', 'instant'))

watch(selected, (newVal) => {
  selectedTotal.value === 0
    ? localStorage.removeItem('selected-people')
    : localStorage.setItem('selected-people', JSON.stringify(newVal))
}, { deep: true })

nextTick(async () => {
  await loadData()

  // галочки по умолчанию
  if (sendEventId) {
    const { clients, teachers } = people.value
    selected.value = {
      clients: clients.map(e => e.id),
      teachers: teachers.map(e => e.id),
    }
  }
  else if (participantsEventId) {
    const { data } = await useHttp<EventResource>(`events/${participantsEventId}`)
    const { clients, teachers } = data.value!.participants!
    selected.value = {
      clients: clients.map(e => e.entity.id),
      teachers: teachers.map(e => e.entity.id),
    }
  }
  else {
    const selectedPeople = localStorage.getItem('selected-people')
    if (selectedPeople) {
      selected.value = JSON.parse(selectedPeople) as RecipientIds
    }
  }
})
</script>

<template>
  <UiIndexPage class="people-selector" :data="{ loading, noData }">
    <template #filters>
      <!-- <GroupMessageFilters v-model="filters" /> -->
      <v-select
        v-model="mode"
        label="Режим"
        :items="selectItems(RecepientLabel)"
        density="comfortable"
        class="lowercase-select-items"
        :menu-props="{
          class: 'lowercase-select-items',
        }"
      />
      <template v-if="mode === 'clients'">
        <UiMultipleSelect
          v-model="clientLiveFilters.directions"
          :items="selectItems(DirectionLabel)"
          label="Направления"
          density="comfortable"
        />
        <v-text-field
          v-model="clientLiveFilters.q"
          label="Поиск"
          density="comfortable"
        />
      </template>
      <div v-if="!!selectedTotal" class="people-selector__controls">
        <div>
          <v-btn variant="text" @click="clearSelection()">
            отмена
          </v-btn>
        </div>
        <div>
          <v-btn v-if="participantsEventId" color="primary" :loading="saving" @click="saveParticipants()">
            сохранить
            ({{ selectedTotal }})
          </v-btn>
          <v-btn
            v-else color="primary" @click="$router.push({
              name: 'people-selector-send',
              query: {
                event_id: sendEventId,
              },
            })"
          >
            отправить
            ({{ selectedTotal }})
          </v-btn>
        </div>
      </div>
    </template>
    <div class="table">
      <div class="people-selector__item">
        <div @click="selectAll()">
          <UiCheckbox :value="isSelectedAll" />
          <span> всего: {{ people[mode].length }} </span>
          <span v-if="people[mode].length !== itemsFiltered.length" class="text-gray ml-2">
            найдено: {{ itemsFiltered.length }}
          </span>
        </div>
        <div></div>
      </div>
      <v-virtual-scroll :key="mode && clientLiveFilters.q" :items="itemsFiltered" item-key="id">
        <template #default="{ item }">
          <div class="people-selector__item" @click="select(item)">
            <div>
              <UiCheckbox :value="isSelected(item)" />
              <UiPerson :item="item" teacher-format="full" />
            </div>
            <div>
              <template v-if="item.directions">
                {{ item.directions.map(e => DirectionLabel[e]).join(', ') }}
              </template>
            </div>
          </div>
        </template>
      </v-virtual-scroll>
    </div>
  </UiIndexPage>
</template>

<style lang="scss">
.people-selector {
  &__controls {
    display: flex;
    align-items: center;
    width: initial !important;
    flex: 1;
    justify-content: flex-end;
    gap: 10px;
  }

  &__filters {
    .filters__inputs {
      width: 100%;
      & > .v-input {
        width: 250px;
        max-width: 250px;
      }
    }
  }

  &__item {
    cursor: default;
    user-select: none;

    & > div {
      &:first-child {
        width: 500px;
        display: flex;
        align-items: center;
        gap: 8px;
      }
    }
  }
}
</style>
