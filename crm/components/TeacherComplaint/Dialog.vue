<script lang="ts" setup>
import type { TeacherComplaintResource } from '.'
import { apiUrl, modelDefaults, TeacherComplaintStatusLabel } from '.'

const items = defineModel<TeacherComplaintResource[]>({ required: true })

const { item, expose, dialog, dialogData } = useCrud<TeacherComplaintResource, TeacherComplaintResource>(
  apiUrl,
  modelDefaults,
  items,
)


defineExpose(expose)
</script>

<template>
  <CrudDialog v-model="dialog" :data="dialogData">
    <template #title-create>
      Добавить нарушение
    </template>
    <template #title-edit>
      Редактировать жалобу
    </template>
    <div>
      <v-text-field disabled :model-value="formatName(item.teacher, 'full')" label="Преподаватель" v-if="item.teacher" />
    </div>
    <div>
      <v-select
        v-model="item.status"
        label="Статус"
        :items="selectItems(TeacherComplaintStatusLabel)"
      />
    </div>
  </CrudDialog>
</template>

