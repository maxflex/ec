<script setup lang="ts">
import type { ComplaintDialog } from '#components'
import type { ComplaintListResource } from '.'
import { apiUrl } from '.'

const { clientId, teacherId } = defineProps<{
  clientId?: number
  teacherId?: number
}>()
const filters = useAvailableYearsFilter()
const dialog = ref<InstanceType<typeof ComplaintDialog>>()
const { items, indexPageData, reloadData, availableYears } = useIndex<ComplaintListResource>(
  apiUrl,
  filters,
  {
    loadAvailableYears: true,
    staticFilters: {
      teacher_id: teacherId,
      client_id: clientId,
    },
  },
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <AvailableYearsSelector v-model="filters.year" :items="availableYears" />
    </template>
    <template v-if="clientId" #buttons>
      <v-btn
        color="primary"
        @click="dialog?.create({
          client_id: clientId,
        })"
      >
        добавить жалобу
      </v-btn>
    </template>
    <ComplaintList :items="items" :teacher-id="teacherId" :client-id="clientId" @edit="dialog?.edit" />
  </UiIndexPage>
  <ComplaintDialog ref="dialog" v-model="items" @updated="reloadData" />
</template>
