<script setup lang="ts">
import type { ClientsBrowseResource } from '~/components/Client'

const year = currentAcademicYear()
const nextYear = year + 1 as Year

const filters = ref<YearFilters>({
  year: currentAcademicYear(),
})

const { indexPageData, items } = useIndex<ClientsBrowseResource>(
  `clients-browse`,
  filters,
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <v-btn
        v-for="y in [year, nextYear]" :key="y"
        :color="filters.year === y ? 'primary' : 'bg'"
        @click="filters.year = y"
      >
        {{ y }}–{{ y + 1 }}
      </v-btn>
    </template>
    <template #buttons>
      <UiQuestionTooltip>
        На данной странице отображаются клиенты, допущенные ко входу в личный кабинет.
        Доступ закрывается 30 июня {{ filters.year + 1 }} для договоров {{ YearLabel[filters.year] }} или в случае расторжения
      </UiQuestionTooltip>
    </template>
    <ClientBrowseList :items="items" />
  </UiIndexPage>
</template>
