<script lang="ts" setup>
import type { ViolationResource } from '.'
import { apiUrl, modelDefaults } from '.'

const items = defineModel<ViolationResource[]>({ required: true })
const clientLessons = ref<ClientLessonResource[]>([])

const { item, expose, dialog, dialogData } = useCrud<ViolationResource, ViolationResource>(
  apiUrl,
  modelDefaults,
  items,
  {
    afterOpen: loadClientLessons,
  },
)

async function loadClientLessons() {
  clientLessons.value = []
  const { data } = await useHttp<ApiResponse<ClientLessonResource>>(
    `client-lessons`,
    {
      params: {
        lesson_id: item.value.lesson_id,
      },
    },
  )

  clientLessons.value = data.value!.data
}

defineExpose(expose)
</script>

<template>
  <CrudDialog v-model="dialog" :data="dialogData" class="violation-dialog">
    <template #title-create>
      Добавить нарушение
    </template>
    <template #title-edit>
      Редактировать нарушение
    </template>
    <!-- <UiLoader v-if="clientLessons.length === 0" fixed /> -->
    <Table>
      <TableRow v-for="cl in clientLessons" :key="cl.id">
        <TableCol :width="250">
          <UiAvatar :item="cl.client" :size="40" class="mr-4" />
          <UiPerson :item="cl.client" @click.stop />
        </TableCol>
        <TableCol :width="80">
          {{ ClientLessonStatusLabel[cl.status] }}
        </TableCol>
        <TableCol>
          <div class="violation-dialog__switch">
            <v-switch :model-value="cl.id === item.client_lesson_id" @click="item.client_lesson_id = cl.id" />
          </div>
        </TableCol>
      </TableRow>
    </Table>
    <div>
      <FilePhotoUploader v-model="item.photo" folder="violations" />
    </div>
    <div>
      <FileVideoUploader v-model="item.video" folder="violations" />
    </div>
    <div>
      <v-checkbox
        v-model="item.is_resolved"
        label="Нарушение обработано"
      />
    </div>

    <!-- <pre>
      {{ clientLessons }}
    </pre> -->
  </CrudDialog>
</template>

<style lang="scss">
.violation-dialog {
  .dialog-body {
    padding-top: 0 !important;
  }

  &__switch {
    display: flex;
    justify-content: flex-end;

    .v-switch {
      top: 2px !important;
    }
  }
}
</style>
