<script setup lang="ts">
interface Filters {
  type?: number
}

const { clientId, teacherId } = defineProps<{
  clientId?: number
  teacherId?: number
}>()

const tabName = clientId ? 'ClientReviewTab' : 'TeacherClientReviewTab'
const filters = ref<Filters>(loadFilters({}, tabName))

const { items, indexPageData } = useIndex<ClientReviewListResource, Filters>(
  `client-reviews`,
  filters,
  {
    tabName,
    staticFilters: {
      client_id: clientId,
      teacher_id: teacherId,
      with: clientId ? 'client' : 'teacher',
    },
  },
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <UiClearableSelect
        v-model="filters.type"
        label="Тип"
        :items="yesNo('созданные', 'требуется создание')"
        density="comfortable"
      />
    </template>
    <ClientReviewList :items="items" />
  </UiIndexPage>
</template>
