<script setup lang="ts">
import type { TeacherActDialog, TeacherContractDialog } from '#build/components'
import type { TeacherContractListResource } from '.'
import type { TeacherActListResource } from '../TeacherAct'
import { apiUrl } from '.'

const { teacherId } = defineProps<{ teacherId: number }>()
const teacherContractDialog = ref<InstanceType<typeof TeacherContractDialog>>()
const teacherActDialog = ref<InstanceType<typeof TeacherActDialog>>()
const filters = useAvailableYearsFilter()
const acts = ref<TeacherActListResource[]>()

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

async function loadActs() {
  const { data } = await useHttp<ApiResponse<TeacherActListResource>>(`teacher-acts`, {
    params: {
      teacher_id: teacherId,
    },
  })
  acts.value = data.value!.data
}

nextTick(loadActs)
</script>

<template>
  <UiIndexPage :data="{ loading: indexPageData.loading, noData: (!!items && !items.length) && (!!acts && !acts.length) }">
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
    <TeacherActList v-if="acts" :items="acts" @edit="teacherActDialog?.edit" />
  </UiIndexPage>
  <TeacherContractDialog ref="teacherContractDialog" @updated="reloadData" />
  <TeacherActDialog ref="teacherActDialog" :items="acts" />
</template>
