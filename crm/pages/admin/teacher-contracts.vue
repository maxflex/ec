<script setup lang="ts">
import type { TeacherContractDialog } from '#components'
import type { TeacherContractListResource } from '~/components/TeacherContract'
import { apiUrl } from '~/components/TeacherContract'

const filters = ref<{
  year: Year
  has_problems?: number
  is_active?: number
}>(loadFilters({
  year: currentAcademicYear(),
}))

const dialog = ref<InstanceType<typeof TeacherContractDialog>>()
const { items, indexPageData } = useIndex<TeacherContractListResource>(
  apiUrl,
  filters,
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <v-select
        v-model="filters.year"
        :items="selectItems(YearLabel)"
        label="Учебный год"
        density="comfortable"
      />
      <UiClearableSelect
        v-model="filters.is_active"
        density="comfortable"
        label="Версия"
        :items="yesNo('активная', 'первая')"
        expand
      />
      <UiClearableSelect
        v-model="filters.has_problems"
        density="comfortable"
        label="Есть несоответствия"
        :items="yesNo()"
        expand
      />
    </template>
    <TeacherContractList :items="items" @edit="dialog?.edit" />
  </UiIndexPage>
  <TeacherContractDialog ref="dialog" v-model="items" />
</template>
