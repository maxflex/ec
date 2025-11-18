<script setup lang="ts">
import type { EventResource } from '~/components/Event'
import type { PeopleSelectorResource } from '~/components/PeopleSelector'
import { PeopleSelectorLabel } from '~/components/PeopleSelector'

const route = useRoute()
const router = useRouter()
const eventId = Number.parseInt(route.params.id as string)
const event = ref<EventResource>()
const people = ref<PeopleSelectorResource[]>()
const saving = ref(false)
const selected = ref<Set<string>>(new Set())

const filters = ref<{
  entity_type: EntityType
  directions: Direction[]
  q: string
  // ранее отмечен в событии
  was_added?: number
}>({
  entity_type: EntityTypeValue.client,
  directions: [],
  q: '',
})

function makeKey(p: PeopleSelectorResource) {
  return `${p.id}-${p.entity_type}`
}

async function loadEvent() {
  const { data } = await useHttp<EventResource>(`events/${eventId}`)
  event.value = data.value!
}

async function loadParticipants() {
  const { data } = await useHttp<PeopleSelectorResource[]>(
    `people-selector/get-for-event/${eventId}`,
  )
  people.value = data.value!
  selected.value = new Set(people.value
    .filter(p => p.event_participant)
    .map(p => makeKey(p)),
  )
}

const displayedPeople = computed<PeopleSelectorResource[]>(() => {
  if (!people.value) {
    return []
  }

  const q = filters.value.q.toLowerCase()
  const directions = filters.value.directions

  return people.value
  // Фильтр по типу (Client / Teacher)
    .filter(p => p.entity_type === filters.value.entity_type)
  // Фильтр по имени
    .filter((p) => {
      if (!q) {
        return true
      }

      const fullName = [
        p.last_name ?? '',
        p.first_name ?? '',
        p.middle_name ?? '',
      ].join(' ').toLowerCase()

      return fullName.includes(q)
    })
    // Фильтр по направлениям
    .filter((p) => {
      if (directions.length === 0 || p.entity_type !== EntityTypeValue.client) {
        return true
      }

      const personDirections = p.directions ?? []

      // пересечение массивов
      return personDirections.some(d => directions.includes(d.direction))
    })
    // Фильтр "отмечен в событии?"
    .filter((p) => {
      const wasAdded = filters.value.was_added

      if (wasAdded === undefined) {
        return true
      }

      return wasAdded ? p.event_participant : !p.event_participant
    })
})

const isSelectedAll = computed(() => {
  if (!displayedPeople.value.length) {
    return false
  }
  return displayedPeople.value.every(p => isSelected(p))
})

function isSelected(p: PeopleSelectorResource): boolean {
  return selected.value.has(makeKey(p))
}

function toggle(p: PeopleSelectorResource) {
  const key = makeKey(p)
  selected.value.has(key)
    ? selected.value.delete(key)
    : selected.value.add(key)
}

function toggleSelectAll() {
  if (isSelectedAll.value) {
    // снимаем выбор с видимых
    displayedPeople.value.forEach((p) => {
      selected.value.delete(makeKey(p))
    })
  }
  else {
    // выбираем всех видимых
    displayedPeople.value.forEach((p) => {
      selected.value.add(makeKey(p))
    })
  }
}

function unselectAll() {
  selected.value = new Set()
}

async function save() {
  saving.value = true
  const { error } = await useHttp(
    `event-participants`,
    {
      method: 'POST',
      body: {
        event_id: eventId,
        selected: [...selected.value],
      },
    },
  )

  if (error.value) {
    saving.value = false
    return
  }

  useGlobalMessage('Участники сохранены', 'success')
  router.push({ name: 'events-id', params: { id: eventId } })
}

nextTick(async () => {
  await loadEvent()
  await loadParticipants()
})
</script>

<template>
  <v-fade-transition>
    <UiLoader v-if="!event || !people" />
  </v-fade-transition>
  <div class="back-to-event">
    <RouterLink v-if="event" :to="{ name: 'events-id', params: { id: event.id } }">
      <UiIconLink prepend icon="$back" class="vf-1">
        вернуться в событие {{ event.name }}
      </UiIconLink>
    </RouterLink>
  </div>
  <UiFilters>
    <v-select v-model="filters.entity_type" density="comfortable" :items="selectItems(PeopleSelectorLabel)" />
    <UiMultipleSelect
      v-model="filters.directions"
      :items="selectItems(DirectionLabel)"
      label="Направления"
      density="comfortable"
      :disabled="filters.entity_type !== EntityTypeValue.client"
    />
    <UiClearableSelect
      v-model="filters.was_added"
      label="Отмечен в событии"
      density="comfortable"
      :items="yesNo()"
    />
    <v-text-field v-model="filters.q" label="Поиск" density="comfortable" />

    <template #buttons>
      <v-btn color="primary" :loading="saving" @click="save()">
        сохранить ({{ selected.size }})
      </v-btn>
    </template>
  </UiFilters>
  <Table class="table--padding flex-start">
    <TableRow @click="toggleSelectAll()">
      <TableCol>
        <div class="d-flex ga-2 align-center">
          <UiCheckbox :value="isSelectedAll" />
          <span>всего: {{ displayedPeople.length }}</span>
          <a v-if="selected.size" class="ml-4 cursor-pointer" @click.stop="unselectAll()">отменить всё</a>
        </div>
      </TableCol>
    </TableRow>
    <TableRow v-for="p in displayedPeople" :key="makeKey(p)" @click="toggle(p)">
      <TableCol :width="350">
        <div class="d-flex ga-2 align-center">
          <UiCheckbox :value="isSelected(p)" />
          <UiPerson :item="p" />
        </div>
      </TableCol>
      <TableCol :width="250">
        <ClientDirections v-if="p.directions" :items="p.directions" />
      </TableCol>
      <TableCol>
        <span v-if="p.event_participant">
          отмечен в событии
        </span>
      </TableCol>
    </TableRow>
  </Table>
</template>

<style lang="scss">
.page-events-id-participants {
  $backToEventHeight: 36px;

  .back-to-event {
    position: sticky;
    top: 0;
    padding: 0 var(--padding);
    height: $backToEventHeight;
    min-height: $backToEventHeight;
    background-color: rgb(var(--v-theme-bg));
    font-size: 14px;
    border-bottom: 1px solid rgb(var(--v-theme-border));
    z-index: 1;
    display: flex;
    align-items: center;
    // justify-content: flex-end;
  }
  .filters {
    position: sticky !important;
    top: $backToEventHeight;
    z-index: 1;
    &__inputs > div {
      width: 220px !important;
    }
  }

  .table-row {
    user-select: none;
  }
}
</style>
