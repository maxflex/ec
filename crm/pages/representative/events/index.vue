<script setup lang="ts">
import type { EventListResource } from '~/components/Event'

const { items, indexPageData } = useIndex<EventListResource>(
  `events`,
  ref({}),
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <EventListForLk :items="items" />
  </UiIndexPage>
</template>
