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
      <v-tooltip bottom :width="440">
        <template #activator="{ props }">
          <div class="browse-info" v-bind="props">
            ?
          </div>
        </template>
        На данной странице отображаются клиенты, допущенные ко входу в личный кабинет.
        Доступ закрывается 30 июня {{ filters.year + 1 }} для договоров {{ YearLabel[filters.year] }} или в случае расторжения
      </v-tooltip>
    </template>
    <ClientBrowseList :items="items" />
  </UiIndexPage>
</template>

<style lang="scss">
.browse-info {
  $size: 44px;
  height: $size;
  width: $size;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 2px solid rgb(var(--v-theme-border));
  border-radius: 24px;
  color: rgb(var(--v-theme-gray));
  font-weight: bold;
  user-select: none;
}
</style>
