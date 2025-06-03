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
      <v-tooltip bottom :width="400">
        <template #activator="{ props }">
          <div class="browse-info" v-bind="props">
            ?
          </div>
        </template>
        Ученики, имеющие договоры {{ year }}–{{ nextYear }} (пропуск активен до 30 июня {{ nextYear }} года или до момента расторжения договора) и {{ nextYear }}–{{ nextYear + 1 }} (пропуск активен до 30 июня {{ nextYear + 1 }} года или до момента расторжения договора) учебных лет имеют постоянный пропуск и допущены на посту охраны института
      </v-tooltip>
    </template>
    <ClientBrowseList :items="items" />
  </UiIndexPage>
</template>

<style lang="scss">
.browse-info {
  $size: 42px;
  height: $size;
  width: 52px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  border: 1px solid rgb(var(--v-theme-border));
  border-radius: 24px;
  color: rgb(var(--v-theme-gray));
  font-weight: 500;
}
</style>
