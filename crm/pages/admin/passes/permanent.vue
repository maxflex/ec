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

const year = currentAcademicYear()
const nextYear = year + 1

const { indexPageData, items, total, loading } = useIndex<PersonResource>(`passes/permanent`, filters)

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
      <span>
        всего: {{ total }}
      </span>
      <span v-if="items.length !== itemsFiltered.length" class="text-gray ml-2">
        найдено: {{ itemsFiltered.length }}
      </span>
    </template>
    <template #header>
      <v-table>
        <tbody>
          <tr>
            <td class="permanent-pass-info" style="">
              <template v-if="filters.entity === user">
                Администраторы со статусом "действующий сотрудник"
              </template>
              <template v-else-if="filters.entity === teacher">
                Преподаватели со статусом "ведет занятия сейчас"
              </template>
              <template v-else>
                Ученики, имеющие договоры {{ year }}–{{ nextYear }} (пропуск активен до 30 июня {{ nextYear }} года или до момента расторжения договора) и {{ nextYear }}–{{ nextYear + 1 }} (пропуск активен до 30 июня {{ nextYear + 1 }} года или до момента расторжения договора) учебных лет имеют постоянный пропуск и допущены на посту охраны института
              </template>
            </td>
          </tr>
          <tr style="display: none">
            <td />
          </tr>
        </tbody>
      </v-table>
    </template>
    <v-table>
      <tbody>
        <tr v-for="item in itemsFiltered" :key="item.id">
          <td>
            <UiPerson :item="item" :no-link="filters.entity === EntityTypeValue.user" />
          </td>
        </tr>
      </tbody>
    </v-table>
  </UiIndexPage>
</template>

<style lang="scss">
.permanent-pass-info {
  $padding: 20px !important;
  background: #f5f5f5;
  padding-top: $padding;
  padding-bottom: $padding;
}
</style>
