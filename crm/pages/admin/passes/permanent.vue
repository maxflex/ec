<script setup lang="ts">
const entityTypeLabel = {
  [EntityTypeValue.client]: 'ученики и представители',
  [EntityTypeValue.teacher]: 'преподаватели',
  [EntityTypeValue.user]: 'администраторы',
} as const

interface PassesPermanentResource extends PersonResource {
  directions?: ClientDirections
  representative?: PersonResource
}

interface Filters {
  entity: EntityType
}

const q = ref<string>('')

const filters = ref<Filters>({
  entity: EntityTypeValue.client,
})

const currentYear = currentAcademicYear()
const nextYear = currentYear + 1 as Year

const { items, indexPageData } = useIndex<PassesPermanentResource>(`passes/permanent`, filters)

const itemsFiltered = computed<PassesPermanentResource[]>(() => {
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
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <v-select
        v-model="filters.entity"
        density="comfortable"
        label="Тип"
        :items="selectItems(entityTypeLabel)"
      />
      <v-text-field
        v-model="q"
        label="Поиск"
        density="comfortable"
      />
      <span>
        всего: {{ itemsFiltered.length }}
      </span>
    </template>
    <template #buttons>
      <UiQuestionTooltip>
        <template v-if="filters.entity === EntityTypeValue.user">
          На данной странице отображаются администраторы, имеющие активный постоянный пропуск.
          Пропуск активен для администраторов в статусе "действующий сотрудник"
        </template>
        <template v-else-if="filters.entity === EntityTypeValue.teacher">
          На данной странице отображаются преподаватели, имеющие активный постоянный пропуск.
          Пропуск активен только для преподавателей в статусе "ведет занятия сейчас"
        </template>
        <template v-else>
          На данной странице отображаются ученики и представители, имеющие активный постоянный пропуск.
          Доступ закрывается 30 июня {{ nextYear }} для договоров {{ YearLabel[currentYear] }} и 30 июня {{ nextYear + 1 }}
          для договоров {{ YearLabel[nextYear] }} или в случае их расторжения
        </template>
      </UiQuestionTooltip>
    </template>
    <div class="table table--padding">
      <div v-for="item in itemsFiltered" :key="item.id">
        <div style="width: 300px">
          <UiPerson :item="item" :no-link="filters.entity === EntityTypeValue.user" />
          <div v-if="item.representative">
            <UiPerson :item="item.representative" />
          </div>
        </div>
        <div>
          <ClientDirections v-if="item.directions" :item="item.directions" />
        </div>
      </div>
    </div>
  </UiIndexPage>
</template>
