<script setup lang="ts">
import type { ClientReviewListResource } from '.'
import { apiUrl } from '.'

interface Filters {
  requirement?: number
}

const { clientId, teacherId } = defineProps<{
  clientId?: number
  teacherId?: number
}>()

const tabName = clientId ? 'ClientReviewTab' : 'TeacherClientReviewTab'
const filters = ref<Filters>(loadFilters({}, tabName))

const { items, indexPageData } = useIndex<ClientReviewListResource>(
  apiUrl,
  filters,
  {
    tabName,
    staticFilters: {
      client_id: clientId,
      teacher_id: teacherId,
    },
  },
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <UiClearableSelect
        v-model="filters.requirement"
        label="Требование отзыва"
        :items="yesNo('созданные', 'требуется создание')"
        density="comfortable"
      />
    </template>
    <ClientReviewList :items="items" :client-id="clientId" :teacher-id="teacherId" />
  </UiIndexPage>
</template>
