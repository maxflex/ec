<script setup lang="ts">
import type { TeacherComplaintDialog } from '#components'
import type { TeacherComplaintRecipient, TeacherComplaintResource, TeacherComplaintStatus } from '~/components/TeacherComplaint'
import { TeacherComplaintRecipientLabel, TeacherComplaintStatusLabel } from '~/components/TeacherComplaint'

const filters = ref<{
  status?: TeacherComplaintStatus
  recipient?: TeacherComplaintRecipient
}>(loadFilters({
  status: undefined,
  recipient: undefined,
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
      <UiClearableSelect
        v-model="filters.recipient"
        density="comfortable"
        label="Кому адресована"
        :items="selectItems(TeacherComplaintRecipientLabel)"
        expand
      />
    </template>
    <TeacherComplaintList :items="items" @edit="dialog?.edit" />
  </UiIndexPage>
  <TeacherComplaintDialog ref="dialog" v-model="items" />
</template>
