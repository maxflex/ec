<script setup lang="ts">
import { ClientDirections } from '#components'

const currentYear = currentAcademicYear()
const nextYear = (currentYear + 1) as Year
const modeLabel = {
  clientsCurrentYear: `клиенты ${currentYear}-${nextYear}`,
  clientsNextYear: `клиенты ${nextYear}-${nextYear + 1}`,
  teachers: 'преподаватели',
} as const

type Mode = keyof typeof modeLabel

const route = useRoute()
const router = useRouter()
const loading = ref(true)

// отправка сообщения по событию
const sendEventId = route.query.event_id

// редактирование участников события
const participantsEventId = route.query.participants

const saving = ref(false)
const mode = ref<Mode>('clientsCurrentYear')
// const mode = ref<Recepient>('clients')
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

const currentPeople = computed<RecepientPerson[]>(() => {
  if (mode.value === 'clientsCurrentYear') {
    return people.value.clients.filter(c => c.years!.includes(currentYear))
  }
  if (mode.value === 'clientsNextYear') {
    return people.value.clients.filter(c => c.years!.includes(nextYear))
  }
  return people.value.teachers
})

const itemsFiltered = computed<RecepientPerson[]>(() => {
  if (mode.value === 'teachers') {
    return people.value.teachers
  }

  const { q, directions } = clientLiveFilters.value
  const query = q.trim().toLowerCase()
  const year = mode.value === 'clientsCurrentYear' ? currentYear : nextYear

  return currentPeople.value.filter((c) => {
    const nameMatch = query
      ? [c.last_name, c.first_name].join(' ').toLowerCase().includes(query)
      : true

    const clientDirections = c.directions![year] ?? []

    const directionsMatch = directions.length
      ? directions.some(d => clientDirections.includes(d))
      : true

    return nameMatch && directionsMatch
  })
})

const currentMode = computed<Recepient>(() => (mode.value === 'teachers' ? 'teachers' : 'clients'))

const isSelectedAll = computed<boolean>(() => {
  for (const p of itemsFiltered.value) {
    if (!selected.value[currentMode.value].includes(p.id)) {
      return false
    }
  }

  return true
})

const selectedTotal = computed(() => {
  const { clients, teachers } = selected.value
  return clients.length + teachers.length
})

async function loadData() {
  loading.value = true
  const { data } = await useHttp<Recipients>(`people-selector`, {
    params: {
      event_id: sendEventId,
    },
  })
  people.value = data.value!
  loading.value = false
}

function select(item: RecepientPerson) {
  const index = selected.value[currentMode.value].findIndex(id => id === item.id)
  index === -1
    ? selected.value[currentMode.value].push(item.id)
    : selected.value[currentMode.value].splice(index, 1)
}

function selectAll() {
  if (isSelectedAll.value) {
    for (const p of itemsFiltered.value) {
      const index = selected.value[currentMode.value].findIndex(id => id === p.id)
      if (index !== -1) {
        selected.value[currentMode.value].splice(index, 1)
      }
    }
    return
  }

  for (const p of itemsFiltered.value) {
    const exists = selected.value[currentMode.value].includes(p.id)
    if (!exists) {
      selected.value[currentMode.value].push(p.id)
    }
  }
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
  await useHttp(`event-participants`, {
    method: 'post',
    body: {
      id: participantsEventId,
      selected: selected.value,
    },
  })
  await router.push({ name: 'events-id', params: { id: participantsEventId as string } })
}

function isSelected(item: RecepientPerson): boolean {
  return isSelectedAll.value || selected.value[currentMode.value].includes(item.id)
}

watch(mode, () => smoothScroll('main', 'top', 'instant'))

watch(
  selected,
  (newVal) => {
    selectedTotal.value === 0
      ? localStorage.removeItem('selected-people')
      : localStorage.setItem('selected-people', JSON.stringify(newVal))
  },
  { deep: true },
)

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
        :items="selectItems(modeLabel)"
        density="comfortable"
        class="lowercase-select-items"
        :menu-props="{
          class: 'lowercase-select-items',
        }"
      />
      <template v-if="currentMode === 'clients'">
        <UiMultipleSelect
          v-model="clientLiveFilters.directions"
          :items="selectItems(DirectionLabel)"
          label="Направления"
          density="comfortable"
        />
        <v-text-field v-model="clientLiveFilters.q" label="Поиск" density="comfortable" />
      </template>
      <div v-if="!!selectedTotal" class="people-selector__controls">
        <div>
          <v-btn variant="text" @click="clearSelection()">
            отмена
          </v-btn>
        </div>
        <div>
          <v-btn
            v-if="participantsEventId"
            color="primary"
            :loading="saving"
            @click="saveParticipants()"
          >
            сохранить ({{ selectedTotal }})
          </v-btn>
          <v-btn
            v-else
            color="primary"
            @click="
              $router.push({
                name: 'people-selector-send',
                query: {
                  event_id: sendEventId,
                },
              })
            "
          >
            отправить ({{ selectedTotal }})
          </v-btn>
        </div>
      </div>
    </template>
    <div class="table">
      <div class="people-selector__item">
        <div @click="selectAll()">
          <UiCheckbox :value="isSelectedAll" />
          <span> всего: {{ itemsFiltered.length }} </span>
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
              <ClientDirections v-if="item.directions" :item="item.directions" hide-old />
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
        width: 350px;
        display: flex;
        align-items: center;
        gap: 8px;
      }
    }

    .client-directions {
      display: flex;
      align-items: center;
      & > div {
        width: 180px;
      }
    }
  }
}
</style>
