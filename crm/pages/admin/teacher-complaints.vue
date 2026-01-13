<script setup lang="ts">
import type { TeacherComplaintDialog } from '#components'
import { TeacherComplaintStatusLabel, type TeacherComplaintResource, type TeacherComplaintStatus } from '~/components/TeacherComplaint'

const filters = ref<{ status?: TeacherComplaintStatus }>(loadFilters({
  status: undefined
}))

const dialog = ref<InstanceType<typeof TeacherComplaintDialog>>()
const { items, indexPageData } = useIndex<TeacherComplaintResource>(
  `teacher-complaints`,
  filters,
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <UiClearableSelect
        v-model="filters.status"
        density="comfortable"
        label="Статус"
        :items="selectItems(TeacherComplaintStatusLabel)"
      />
    </template>
    <TeacherComplaintList :items="items"  @edit="dialog?.edit" />
  </UiIndexPage>
  <TeacherComplaintDialog ref="dialog" v-model="items" />
</template>
