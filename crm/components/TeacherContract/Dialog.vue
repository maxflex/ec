<script lang="ts" setup>
import type { TeacherContractData, TeacherContractListResource, TeacherContractResource } from '.'
import { apiUrl, modelDefaults } from '.'

const items = defineModel<TeacherContractListResource[]>({ required: true })

const { item, expose, dialog, dialogData } = useCrud<TeacherContractResource, TeacherContractListResource>(
  apiUrl,
  modelDefaults,
  items,
  {
    afterOpen: loadData,
  },
)

async function loadData() {
  item.value.data = null
  const { data } = await useHttp<TeacherContractData>(
    `${apiUrl}/load-data`,
    {
      method: 'POST',
      body: {
        teacher_id: item.value.teacher_id,
        year: item.value.year,
      },
    },
  )
  item.value.data = data.value
}

watch(() => item.value.year, loadData)

defineExpose(expose)
</script>

<template>
  <CrudDialog v-model="dialog" :data="dialogData">
    <template #title-create>
      Добавить договор
    </template>
    <template #title-edit>
      Редактировать договор
    </template>
    <div class="double-input">
      <UiYearSelector v-model="item.year" />
    </div>
    <div>
      <UiDateInput v-model="item.date" today-btn />
    </div>
    <div>
      <FileUploader v-model="item.file" folder="teacher-contracts" class="mt-0" label="прикрепить PDF" />
    </div>
    <v-fade-transition>
      <div v-if="item.data && !item.data.length" class="relative" style="display: inline-block; height: 300px">
        <UiNoData />
      </div>
      <table v-else-if="item.data" class="dialog-table">
        <thead>
          <tr>
            <th>группа</th>
            <th>занятий</th>
            <th>по стоимости</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="d in item.data" :key="`${d.group_id}-${d.price}-${d.cnt}`" class="padding">
            <td>
              ГР-{{ d.group_id }}
            </td>
            <td>
              {{ d.cnt }}
            </td>
            <td>
              {{ formatPrice(d.price) }} руб.
            </td>
          </tr>
          <tr>
            <td></td>
            <td></td>
            <td></td>
          </tr>
        </tbody>
      </table>
    </v-fade-transition>
  </CrudDialog>
</template>
