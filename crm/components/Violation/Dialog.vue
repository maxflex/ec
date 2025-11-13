<script lang="ts" setup>
import type { ViolationResource } from '.'
import { orderBy } from 'lodash-es'
import { apiUrl, modelDefaults } from '.'

const items = defineModel<ViolationResource[]>({ required: true })
const clientLessons = ref<SelectItems>([])

const { item, expose, dialog, dialogData } = useCrud<ViolationResource, ViolationResource>(
  apiUrl,
  modelDefaults,
  items,
  {
    afterOpen: loadClientLessons,
  },
)

async function loadClientLessons() {
  const { data } = await useHttp<ApiResponse<ClientLessonResource>>(`client-lessons`, {
    params: {
      lesson_id: item.value.lesson_id,
    },
  })

  clientLessons.value = orderBy(data.value!.data.filter(e => e.status !== 'absent').map(e => ({
    value: e.id,
    title: formatName(e.client, 'full'),
  })), 'title')
}

defineExpose(expose)
</script>

<template>
  <CrudDialog v-model="dialog" :data="dialogData">
    <template #title-create>
      Добавить нарушение
    </template>
    <template #title-edit>
      Редактировать нарушение
    </template>
    <div>
      <v-textarea v-model="item.comment" label="Комментарий к нарушению" auto-grow />
    </div>
    <div>
      <UiClearableSelect
        v-model="item.client_lesson_id"
        :loading="clientLessons.length === 0"
        nullify
        :items="clientLessons" label="Ученик"
      />
    </div>
    <div>
      <FileUploader v-model="item.file" folder="violations" label="прикрепить изображение" />
    </div>
    <div>
      <v-checkbox
        v-model="item.is_resolved"
        label="Нарушение обработано"
      />
    </div>
  </CrudDialog>
</template>
