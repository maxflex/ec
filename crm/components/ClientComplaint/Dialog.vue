<script setup lang="ts">
import type { ClientComplaintResource } from '.'
import { apiUrl, modelDefaults } from '.'

const emit = defineEmits<{
  updated: []
}>()
const teachers = ref<TeacherListResource[]>([])
const { item, expose, dialog, dialogData } = useCrud<ClientComplaintResource>(apiUrl, modelDefaults, {
  afterOpen: loadTeachers,
  afterSave() {
    emit('updated')
  }
})

async function loadTeachers() {
  const { data } = await useHttp<ApiResponse<TeacherListResource>>(
    `teachers`,
    {
      params: {
        client_id: item.value.client_id,
      },
    },
  )
  teachers.value = data.value!.data
}

defineExpose(expose)
</script>

<template>
  <CrudDialog v-model="dialog" :data="dialogData">
    <template #title-create>
      Добавить жалобу
    </template>
    <template #title-edit>
      Редактировать жалобу
    </template>
    <div>
      <TeacherSelector v-model="item.teacher_id" :items="teachers" label="Преподаватель" />
    </div>
    <div>
      <UiClearableSelect v-model="item.program" :items="selectItems(ProgramShortLabel)" label="Программа" />
    </div>
    <div>
      <v-textarea v-model="item.text" label="Текст жалобы" auto-grow />
    </div>
  </CrudDialog>
</template>
