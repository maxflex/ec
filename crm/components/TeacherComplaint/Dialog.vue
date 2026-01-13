<script lang="ts" setup>
import type { TeacherComplaintResource } from '.'
import { apiUrl, modelDefaults, TeacherComplaintRecipientLabel, TeacherComplaintStatusLabel } from '.'

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
      <v-select
        v-model="item.status"
        label="Статус"
        :items="selectItems(TeacherComplaintStatusLabel)"
      />
    </div>
    <div>
      <v-text-field v-if="item.teacher" disabled :model-value="formatName(item.teacher, 'full')" label="Преподаватель" />
    </div>
    <div>
      <v-text-field
        disabled :model-value="item.recipient ? TeacherComplaintRecipientLabel[item.recipient] : 'не установлено'"
        label="Кому адресована"
      />
    </div>
    <div>
      <v-textarea :model-value="item.text" disabled label="Текст жалобы" rows="5" auto-grow />
    </div>
  </CrudDialog>
</template>
