<script setup lang="ts">
import type { PeopleSelectorResource } from '~/components/PeopleSelector'
import { PeopleSelectorLabel } from '~/components/PeopleSelector'

// TODO: пока отключили
// отправка сообщения по событию
// const eventId = route.query.event_id

const people = ref<PeopleSelectorResource[]>()
const selected = ref<Set<string>>(new Set())
const filters = ref<{
  entity_type: EntityType
  directions: Direction[]
  q: string
}>({
  entity_type: EntityTypeValue.client,
  directions: [],
  q: '',
})

function makeKey(p: PeopleSelectorResource) {
  return `${p.id}-${p.entity_type}`
}

async function loadData() {
  const { data } = await useHttp<PeopleSelectorResource[]>(`people-selector/get-all`)
  people.value = data.value!
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

watch(selected, (newVal) => {
  newVal.size === 0
    ? localStorage.removeItem('selected-people')
    : localStorage.setItem('selected-people', JSON.stringify([...newVal]))
}, {
  deep: true,
})

nextTick(async () => {
  await loadData()

  // галочки по умолчанию
  // const selectedPeople = localStorage.getItem('selected-people')
  // if (selectedPeople) {
  //   selected.value = JSON.parse(selectedPeople) as RecipientIds
  // }
})
</script>

<template>
  <v-fade-transition>
    <UiLoader v-if="!people" />
  </v-fade-transition>
  <UiFilters>
    <v-select v-model="filters.entity_type" density="comfortable" :items="selectItems(PeopleSelectorLabel)" />
    <UiMultipleSelect
      v-model="filters.directions"
      :items="selectItems(DirectionLabel)"
      label="Направления"
      density="comfortable"
      :disabled="filters.entity_type !== EntityTypeValue.client"
    />
    <v-text-field v-model="filters.q" label="Поиск" density="comfortable" />

    <template #buttons>
      <v-btn
        v-if="selected.size"
        color="primary"
        @click="$router.push({ name: 'group-message-send' })"
      >
        отправить ({{ selected.size }})
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
      <TableCol>
        <ClientDirections v-if="p.directions" :items="p.directions" />
      </TableCol>
    </TableRow>
  </Table>
</template>
