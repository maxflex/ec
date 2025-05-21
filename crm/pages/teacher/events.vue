<script setup lang="ts">
const { user } = useAuthStore()
const { items, indexPageData } = useIndex<EventListResource>(
  `events`,
  ref({}),
  {
    staticFilters: {
      teacher_id: user?.id,
    },
  },
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <EventListForLk :items="items" />
  </UiIndexPage>
</template>
