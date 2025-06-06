<script setup lang="ts">
type LocalEntityType = typeof entityTypes[number]

interface Filters {
  entity: LocalEntityType
}

const { client, clientParent, teacher, user } = EntityTypeValue
const entityTypes = [client, clientParent, teacher, user] as const
const q = ref<string>('')
const selectItemsFiltered = Object.keys(EntityTypeLabel)
  .filter(key => entityTypes.includes(key as LocalEntityType))
  .map(value => ({
    value,
    title: EntityTypeLabel[value as EntityType],
  }))

const filters = ref<Filters>({
  entity: client,
})

const currentYear = currentAcademicYear()
const nextYear = currentYear + 1 as Year

const { items, loading } = useIndex<PersonResource>(`passes/permanent`, filters)

const itemsFiltered = computed<PersonResource[]>(() => {
  const query = q.value.trim().toLowerCase()
  if (!query) {
    return items.value
  }
  return items.value.filter(c => [c.last_name, c.first_name]
    .join(' ')
    .toLowerCase()
    .includes(query),
  )
})
</script>

<template>
  <UiIndexPage :data="{ loading, noData: itemsFiltered.length === 0 }">
    <template #filters>
      <v-select
        v-model="filters.entity"
        density="comfortable"
        label="Тип"
        :items="selectItemsFiltered"
      />
      <v-text-field
        v-model="q"
        label="Поиск"
        density="comfortable"
      />
      <span v-if="!loading">
        всего: {{ itemsFiltered.length }}
      </span>
    </template>
    <template #buttons>
      <UiQuestionTooltip>
        <template v-if="filters.entity === user">
          На данной странице отображаются администраторы, имеющие активный постоянный пропуск.
          Пропуск активен для администраторов в статусе "действующий сотрудник"
        </template>
        <template v-else-if="filters.entity === teacher">
          На данной странице отображаются преподаватели, имеющие активный постоянный пропуск.
          Пропуск активен только для преподавателей в статусе "ведет занятия сейчас"
        </template>
        <template v-else>
          На данной странице отображаются {{ filters.entity === client ? 'ученики' : 'представители' }}, имеющие активный постоянный пропуск.
          Доступ закрывается 30 июня {{ nextYear }} для договоров {{ YearLabel[currentYear] }} и 30 июня {{ nextYear + 1 }}
          для договоров {{ YearLabel[nextYear] }} или в случае их расторжения
        </template>
      </UiQuestionTooltip>
    </template>
    <v-table>
      <tbody>
        <tr v-for="item in itemsFiltered" :key="item.id">
          <td width="300">
            <UiPerson :item="item" :no-link="filters.entity === EntityTypeValue.user" />
          </td>
          <td>
            <ClientDirections v-if="item.directions" :item="item.directions" class="py-4" />
          </td>
        </tr>
      </tbody>
    </v-table>
  </UiIndexPage>
</template>
