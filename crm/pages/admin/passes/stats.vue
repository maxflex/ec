<script setup lang="ts">
interface Filters {
  date_from?: string
  date_to?: string
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
  EntityTypeValue.representative,
  EntityTypeValue.teacher,
  EntityTypeValue.user,
] as EntityType[]

const filters = ref<Filters>(loadFilters({ }))

const { items, indexPageData } = useIndex<PassStats>(
  `passes/stats`,
  filters,
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <div class="double-input-glued">
        <UiDateInput
          v-model="filters.date_from"
          label="Начиная с"
          clearable
          density="comfortable"
        />
        <UiDateInput
          v-model="filters.date_to"
          label="по"
          clearable
          density="comfortable"
        />
      </div>
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
            Кто проходил
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

<style lang="scss">
.page-passes-stats {
  .date-input__today {
    position: absolute;
    bottom: -17px;
  }
}
</style>
