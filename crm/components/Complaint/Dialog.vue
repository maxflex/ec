<script setup lang="ts">
import type { ComplaintListResource, ComplaintResource } from '.'
import type { TeacherListResource } from '../Teacher'
import { apiUrl, modelDefaults } from '.'

const items = defineModel<ComplaintListResource[]>({ required: true })
const teachers = ref<TeacherListResource[]>([])
const programs = ref<Program[]>([])

const { item, expose, dialog, dialogData } = useCrud<ComplaintResource, ComplaintListResource>(
  apiUrl,
  modelDefaults,
  items,
  {
    afterOpen: loadTeachers,
  },
)

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

async function loadPrograms() {
  if (!item.value.teacher_id) {
    programs.value = []
    return
  }
  const { data } = await useHttp<Program[]>(
    `client-lessons`,
    {
      params: {
        pluck: 'program',
        client_id: item.value.client_id,
        teacher_id: item.value.teacher_id,
      },
    },
  )
  programs.value = data.value!
}

watch(() => item.value.teacher_id, async () => {
  await loadPrograms()
  item.value.program = programs.value.length === 1 ? programs.value[0] : undefined
})

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
      <v-select
        v-model="item.year"
        label="Учебный год"
        :items="selectItems(YearLabel)"
      />
    </div>
    <div>
      <TeacherSelector v-model="item.teacher_id" :items="teachers" label="Преподаватель" />
    </div>
    <div>
      <UiClearableSelect
        v-model="item.program"
        label="Программа"
        :items="selectItems(ProgramShortLabel, programs)"
        :disabled="programs.length < 2"
      />
    </div>
    <div>
      <v-textarea v-model="item.text" label="Текст жалобы" auto-grow />
    </div>
  </CrudDialog>
</template>
