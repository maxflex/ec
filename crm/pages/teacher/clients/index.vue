<script setup lang="ts">
import type { ClientListResource } from '~/components/Client'

const { items, indexPageData } = useIndex<ClientListResource>(
  `clients`,
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <ClientList :items="items" />
  </UiIndexPage>
</template>
