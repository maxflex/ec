<script setup lang="ts">
export interface ClientListResource extends PersonResource {
  directions: Direction[]
  created_at: string
}

const filters = ref<YearFilters>(loadFilters({
  year: currentAcademicYear(),
}))

const { items, indexPageData } = useIndex<ClientListResource>(
  `clients`,
  filters,
)
</script>

<template>
  <UiPageTitle>
    Клиенты
  </UiPageTitle>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <ClientFilters v-model="filters" />
    </template>
    <ClientList :items="items" />
  </UiIndexPage>
</template>
