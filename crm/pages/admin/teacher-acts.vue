<script setup lang="ts">
import type { TeacherActDialog, TeacherActMassDialog } from '#components'
import type { TeacherActListResource } from '~/components/TeacherAct'
import { apiUrl } from '~/components/TeacherAct'

const filters = ref<{
  year: Year
}>(loadFilters({
  year: currentAcademicYear(),
}))

const massDialog = ref<InstanceType<typeof TeacherActMassDialog>>()
const dialog = ref<InstanceType<typeof TeacherActDialog>>()
const { items, indexPageData, reloadData } = useIndex<TeacherActListResource>(
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
    </template>
    <template #buttons>
      <v-btn color="primary" @click="massDialog?.open()">
        добавить акты
      </v-btn>
    </template>
    <TeacherActList :items="items" @edit="dialog?.edit" />
  </UiIndexPage>
  <TeacherActMassDialog ref="massDialog" @saved="reloadData" />
  <TeacherActDialog ref="dialog" :items="items" />
</template>
