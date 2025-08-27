<script setup lang="ts">
const { user } = useAuthStore()
const { items, indexPageData } = useIndex<EventListResource>(
  `events`,
  ref({}),
  {
    staticFilters: {
      client_id: user?.id,
    },
  },
)
</script>

<template>
  <UiPageTitle>
    События
  </UiPageTitle>
  <UiIndexPage :data="indexPageData">
    <EventList :items="items" />
  </UiIndexPage>
</template>
