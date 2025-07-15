<script setup lang="ts">
import type { ClientReviewDialog } from '#components'
import type { ClientReviewListResource } from '~/components/ClientReview'
import type { Filters } from '~/components/ClientReview/Filters.vue'
import { apiUrl } from '~/components/ClientReview'

const filters = ref<Filters>({})
const { items, indexPageData } = useIndex<ClientReviewListResource>(apiUrl, filters)
const dialog = ref<InstanceType<typeof ClientReviewDialog>>()
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <ClientReviewFilters v-model="filters" />
    </template>
    <ClientReviewList :items="items" show-client @edit="dialog?.edit" />
  </UiIndexPage>
  <ClientReviewDialog ref="dialog" v-model="items" />
</template>
