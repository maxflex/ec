<script setup lang="ts">
import type { TeacherContractDialog } from '#build/components'
import type { TeacherContractListResource } from '.'
import { apiUrl } from '.'

const { teacherId } = defineProps<{ teacherId: number }>()
const teacherContractDialog = ref<InstanceType<typeof TeacherContractDialog>>()
const filters = useAvailableYearsFilter()

const { items, availableYears, indexPageData, reloadData } = useIndex<TeacherContractListResource>(
  apiUrl,
  filters,
  {
    loadAvailableYears: true,
    staticFilters: {
      teacher_id: teacherId,
    },
  },
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <AvailableYearsSelector
        v-model="filters.year"
        :items="availableYears"
      />
    </template>
    <template #buttons>
      <v-btn color="primary" @click="teacherContractDialog?.create({ teacher_id: teacherId })">
        добавить договор
      </v-btn>
    </template>
    <TeacherContractList highlight-active :items="items" @edit="teacherContractDialog?.edit" />
  </UiIndexPage>
  <TeacherContractDialog ref="teacherContractDialog" @updated="reloadData" />
</template>
