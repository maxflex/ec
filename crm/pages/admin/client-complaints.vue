<script setup lang="ts">
import type { ClientComplaintDialog } from '#components'
import type { ClientComplaintListResource } from '~/components/ClientComplaint'
import type { Filters } from '~/components/ClientComplaint/Filters.vue'
import { apiUrl } from '~/components/ClientComplaint'

const filters = ref<Filters>({})
const { items, indexPageData } = useIndex<ClientComplaintListResource>(apiUrl, filters)
const dialog = ref<InstanceType<typeof ClientComplaintDialog>>()
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <ClientComplaintFilters v-model="filters" />
    </template>
    <ClientComplaintList :items="items" show-client @edit="dialog?.edit" />
  </UiIndexPage>
  <ClientComplaintDialog ref="dialog" v-model="items" />
</template>
