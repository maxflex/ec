<script setup lang="ts">
const filters = ref<ClientFilters>(loadFilters({
  q: '',
  year: currentAcademicYear(),
}))

const { items, indexPageData } = useIndex<ClientListResource, ClientFilters>(
    `clients`,
    filters,
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <ClientFilters v-model="filters" />
    </template>
    <ClientList :items="items" />
  </UiIndexPage>
</template>
