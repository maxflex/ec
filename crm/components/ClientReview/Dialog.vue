<script setup lang="ts">
import type { ClientReviewListResource, ClientReviewResource } from '.'
import type { TeacherListResource } from '../Teacher'
import { apiUrl, modelDefaults } from '.'

const items = defineModel<ClientReviewListResource[]>({ required: true })
const teachers = ref<TeacherListResource[]>([])
const programs = ref<Program[]>([])

const { item, expose, dialog, dialogData } = useCrud<ClientReviewResource, ClientReviewListResource>(
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
    `client/available-programs`,
    {
      params: {
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
      Добавить отзыв
    </template>
    <template #title-edit>
      Редактировать отзыв
    </template>
    <div class="text-center pb-2">
      <v-rating
        v-model="item.rating"
        hover
        active-color="orange"
        color="orange"
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
      <v-textarea v-model="item.text" label="Текст отзыва" auto-grow />
    </div>
  </CrudDialog>
</template>
