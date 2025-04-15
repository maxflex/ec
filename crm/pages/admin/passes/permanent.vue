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

const { indexPageData, items, total } = useIndex<PersonResource, Filters>(`passes/permanent`, filters)
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
      <div class="text-gray">
        всего: {{ total }}
      </div>
    </template>
    <template #header>
      <v-table>
        <tbody>
          <tr>
            <td class="permanent-pass-info" style="">
              <template v-if="filters.entity === user">
                Администраторы с параметром "действующий сотрудник"
              </template>
              <template v-else-if="filters.entity === teacher">
                Преподаватели со статусом "ведет занятия сейчас"
              </template>
              <template v-else>
                <p>
                  {{ filters.entity === client ? 'Ученики' : 'Представители' }}, которые имеют хотя бы 1 нужный договор.
                </p>

                <p class="mt-2">
                  Что такое нужные договоры:
                </p>

                <ul class="pl-6">
                  <li>
                    если сейчас период 01.01.23-31.07.23, то нужными договорами считаются договоры 2023-2024 или 2024-2025 учебных годов
                  </li>
                  <li>
                    если сейчас период 01.08.23-31.12.23, то нужными договорами считаются договора 2024-2025 учебного года
                  </li>
                </ul>
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
        <tr v-for="item in items" :key="item.id">
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
