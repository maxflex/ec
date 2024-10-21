<script setup lang="ts">
type LocalEntityType = typeof entityTypes[number]

interface Filters {
  entity: LocalEntityType
}

const { client, clientParent, teacher, user } = EntityTypeValue
const entityTypes = [client, clientParent, teacher, user] as const
const selectItemsFiltered = Object.keys(EntityTypeLabel)
  .filter(key => entityTypes.includes(key as LocalEntityType))
  .map(value => ({
    value,
    title: EntityTypeLabel[value as EntityType],
  }))

const filters = ref<Filters>({
  entity: client,
})

const { indexPageData, items } = useIndex<PersonResource, Filters>(`passes/permanent`, filters)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <v-select
        v-model="filters.entity"
        density="comfortable"
        label="Тип"
        :items="selectItemsFiltered"
      />
    </template>
    <v-table>
      <tbody>
        <tr v-for="item in items" :key="item.id">
          <td>
            <UiPerson :item="item" :no-link="filters.entity === EntityTypeValue.user" />
          </td>
        </tr>
      </tbody>
    </v-table>
  </UiIndexPage>
</template>
