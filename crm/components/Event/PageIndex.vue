<script setup lang="ts">
import type { EventListResource } from '.'

const { items, indexPageData } = useIndex<EventListResource>(`events`)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <EventListForLk :items="items" />
  </UiIndexPage>
</template>
