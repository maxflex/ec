<script setup lang="ts">
import type { ComplaintDialog } from '#components'
import type { ComplaintListResource } from '~/components/Complaint'
import type { Filters } from '~/components/Complaint/Filters.vue'
import { apiUrl } from '~/components/Complaint'

const filters = ref<Filters>({})
const { items, indexPageData } = useIndex<ComplaintListResource>(apiUrl, filters)
const dialog = ref<InstanceType<typeof ComplaintDialog>>()
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <ComplaintFilters v-model="filters" />
    </template>
    <ComplaintList :items="items" @edit="dialog?.edit" />
  </UiIndexPage>
  <ComplaintDialog ref="dialog" v-model="items" />
</template>
