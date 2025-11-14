<script setup lang="ts">
import type { ViolationDialog } from '#components'
import type { ViolationListResource } from '~/components/Violation'
import { apiUrl } from '~/components/Violation'

export interface Filters {
  is_resolved?: number
  client_lesson_id?: number
}

const filters = ref<Filters>({
})

const { items, indexPageData } = useIndex<ViolationListResource>(apiUrl, filters, {
})

const dialog = ref<InstanceType<typeof ViolationDialog>>()
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <UiClearableSelect v-model="filters.client_lesson_id" density="comfortable" label="Ученик" :items="yesNo('установлен', 'не установлен')" />
      <UiClearableSelect v-model="filters.is_resolved" density="comfortable" label="Статус" :items="yesNo('обработано', 'не обработано')" />
    </template>
    <ViolationList :items="items" @edit="dialog?.edit" />
  </UiIndexPage>
  <ViolationDialog ref="dialog" v-model="items" />
</template>
