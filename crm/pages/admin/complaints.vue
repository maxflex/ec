<script setup lang="ts">
import type { ComplaintDialog } from '#components'
import type { ComplaintListResource } from '~/components/Complaint'
import { apiUrl } from '~/components/Complaint'

export interface Filters {
  year?: Year
  program: Program[]
}

const filters = ref<Filters>({
  year: undefined,
  program: [],
})

const { items, indexPageData, availableYears } = useIndex<ComplaintListResource>(apiUrl, filters, {
  loadAvailableYears: true,
})

const dialog = ref<InstanceType<typeof ComplaintDialog>>()
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <AvailableYearsSelector v-model="filters.year" :items="availableYears" />
      <ProgramSelector
        v-model="filters.program"
        multiple
      />
    </template>
    <ComplaintList :items="items" @edit="dialog?.edit" />
  </UiIndexPage>
  <ComplaintDialog ref="dialog" v-model="items" />
</template>
