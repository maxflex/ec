<script setup lang="ts">
import type { ClientDialog, TeacherDialog, UserDialog } from '#components'

type LocalEntityType = typeof entityTypes[number]

interface Filters {
  entity: LocalEntityType
}

interface ClientsBrowseResource extends PersonResource {
  last_seen_at: string | null
  directions?: Direction[]
  phones: PhoneResource[]
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

const clientDialog = ref<InstanceType<typeof ClientDialog>>()
const teacherDialog = ref<InstanceType<typeof TeacherDialog>>()
const userDialog = ref<InstanceType<typeof UserDialog>>()

const { indexPageData, items, total } = useIndex<ClientsBrowseResource, Filters>(
  `clients-browse`,
  filters,
)

function onEdit(p: ClientsBrowseResource) {
  if (p.entity_type === EntityTypeValue.teacher) {
    teacherDialog.value?.edit(p.id)
    return
  }
  if (p.entity_type === EntityTypeValue.user) {
    userDialog.value?.edit(p.id)
    return
  }
  clientDialog.value?.edit(p.id)
}
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
            <td class="browse-info" style="">
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

                <ui>
                  <li>
                    если сейчас период 01.01.23-31.07.23, то нужными договорами считаются договоры 2023-2024 или 2024-2025 учебных годов
                  </li>
                  <li>
                    если сейчас период 01.08.23-31.12.23, то нужными договорами считаются договора 2024-2025 учебного года
                  </li>
                </ui>
              </template>
            </td>
          </tr>
          <tr style="display: none">
            <td />
          </tr>
        </tbody>
      </v-table>
    </template>
    <div class="table table--padding">
      <div v-for="item in items" :key="item.id">
        <div style="width: 250px">
          <UiPerson :item="item" :no-link="filters.entity === EntityTypeValue.user" />
        </div>
        <div style="width: 200px">
          <UiLastSeenAt :item="item" />
        </div>
        <div style="width: 450px">
          <PhoneList :items="item.phones" show-comment />
        </div>
        <div>
          <template v-if="item.directions">
            {{ item.directions.map(e => DirectionLabel[e]).join(', ') }}
          </template>
        </div>
        <div class="table-actionss">
          <v-btn
            icon="$edit"
            :size="48"
            variant="plain"
            @click="onEdit(item)"
          />
        </div>
      </div>
    </div>
  </UiIndexPage>
  <ClientDialog ref="clientDialog" />
  <TeacherDialog ref="teacherDialog" />
  <UserDialog ref="userDialog" />
</template>

<style lang="scss">
.browse-info {
  $padding: 20px !important;
  background: #f5f5f5;
  padding-top: $padding;
  padding-bottom: $padding;
}
.page-clients-browse {
  .phone-list__comment {
    width: 300px !important;
  }
}
</style>
