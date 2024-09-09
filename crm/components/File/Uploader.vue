<script setup lang="ts">
import { mdiDownload } from '@mdi/js'

const fileInput = ref()

const model = defineModel<UploadedFile[]>({ required: true })

function selectFile() {
  fileInput.value.click()
}

function removeFile(file: UploadedFile) {
  const index = model.value.findIndex(f => f.url === file.url)
  model.value.splice(index, 1)
}

async function onFileSelected(e: Event) {
  const target = e.target as HTMLInputElement
  if (!target.files?.length) {
    return
  }
  const formData = new FormData()
  const file = target.files[0]
  formData.append('file', file)
  const newFile = reactive<UploadedFile>({
    name: file.name,
    size: file.size,
  })
  model.value.push(newFile)
  const { data } = await useHttp<string>(`lessons/upload-file`, {
    method: 'POST',
    body: formData,
  })
  if (data.value) {
    newFile.url = data.value
  }
}
</script>

<template>
  <div class="file-uploader">
    <v-menu
      v-for="file in model"
      :key="file.url || file.name"
    >
      <template #activator="{ props }">
        <FileItem :item="file" v-bind="props" />
      </template>
      <v-list>
        <v-list-item :href="file.url" target="_blank">
          <template #prepend>
            <v-icon :icon="mdiDownload" />
          </template>
          скачать
        </v-list-item>
        <v-list-item @click="removeFile(file)">
          <template #prepend>
            <v-icon icon="$delete" />
          </template>
          удалить
        </v-list-item>
      </v-list>
    </v-menu>
    <div class="mt-2">
      <UiIconLink icon="$file" prepend @click="selectFile()">
        прикрепить файл
      </UiIconLink>
    </div>
    <input
      ref="fileInput"
      style="display: none"
      type="file"
      @change="onFileSelected"
    >
  </div>
</template>

<style lang="scss">
.file-uploader {
  display: flex;
  flex-direction: column;
  gap: 10px;
}
</style>
