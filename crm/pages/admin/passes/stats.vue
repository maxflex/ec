<script setup lang="ts">
interface Filters {
  year: Year
  entity_type?: EntityType
}

interface PassStats {
  entity_id: number
  entity_type: EntityType
  entity: PersonResource
  cnt: number
}

const availableEntityTypes = [
  EntityTypeValue.client,
  EntityTypeValue.clientParent,
  EntityTypeValue.teacher,
  EntityTypeValue.user,
] as EntityType[]

const filters = ref<Filters>(loadFilters({
  year: currentAcademicYear(),
}))

const { items, indexPageData } = useIndex<PassStats, Filters>(
  `passes/stats`,
  filters,
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <v-select
        v-model="filters.year"
        label="Учебный год"
        :items="selectItems(YearLabel)"
        density="comfortable"
      />
      <UiClearableSelect
        v-model="filters.entity_type"
        label="Кто проходил"
        :items="availableEntityTypes.map(value => ({
          value,
          title: EntityTypeLabel[value],
        }))"
        density="comfortable"
      />
    </template>
    <v-table fixed-header height="calc(100vh - 81px)" class="pass-stats">
      <thead>
        <tr>
          <th width="350">
            Имя
          </th>
          <th width="300">
            Тип
          </th>
          <th>
            Проходов
          </th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="item in items" :key="`${item.entity_id}${item.entity_type}`">
          <td>
            <UiPerson :item="item.entity" />
          </td>
          <td>
            {{ EntityTypeLabel[item.entity_type] }}
          </td>
          <td>
            {{ item.cnt }}
          </td>
        </tr>
      </tbody>
    </v-table>
  </UiIndexPage>
</template>
