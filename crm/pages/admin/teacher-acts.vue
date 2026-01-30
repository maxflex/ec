<script setup lang="ts">
import type { TeacherActDialog, TeacherActMassDialog } from '#components'
import type { TeacherActListResource } from '~/components/TeacherAct'
import { apiUrl } from '~/components/TeacherAct'

const exporting = ref(false)
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

async function exportDownload() {
  exporting.value = true
  const { data } = await useHttp<Blob>(
    `${apiUrl}/export`,
    {
      method: 'post',
      responseType: 'blob', // Important: Treat the response as a binary Blob
    },
  )

  // Create a Blob from the response
  const blob = new Blob([data.value!])

  // Create a URL for the Blob
  const url = window.URL.createObjectURL(blob)

  // Create a temporary link and trigger the download
  const link = document.createElement('a')
  link.href = url
  link.download = 'acts.xlsx' // Specify the file name
  document.body.appendChild(link)
  link.click()

  // Clean up
  document.body.removeChild(link)
  window.URL.revokeObjectURL(url)
  exporting.value = false
}
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
      <div class="d-flex ga-2 align-center">
        <v-btn variant="text" :loading="exporting" @click="exportDownload()">
          экспорт
        </v-btn>
        <v-btn color="primary" @click="massDialog?.open()">
          добавить акты
        </v-btn>
      </div>
    </template>
    <TeacherActList :items="items" @edit="dialog?.edit" />
  </UiIndexPage>
  <TeacherActMassDialog ref="massDialog" @saved="reloadData" />
  <TeacherActDialog ref="dialog" :items="items" />
</template>
