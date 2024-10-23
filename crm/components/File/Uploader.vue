<script setup lang="ts">
import { mdiDownload } from '@mdi/js'

const { folder } = defineProps<{
  folder: 'lessons' | 'tests'
}>()

const fileInput = ref()

const model = defineModel<null | UploadedFile | UploadedFile[]>({ required: true })

function selectFile() {
  fileInput.value.click()
}

function removeFile(file: UploadedFile) {
  if (Array.isArray(model.value)) {
    const index = model.value.findIndex(f => f.url === file.url)
    model.value.splice(index, 1)
  }
  else {
    model.value = null
  }
}

function humanFileSize(file: UploadedFile) {
  const { size } = file
  const i = size === 0 ? 0 : Math.floor(Math.log(size) / Math.log(1024))
  const humanFileSize = Number.parseInt((size / 1024 ** i).toFixed(2))
  return (
      `${humanFileSize
      } ${
          ['байт', 'Кб', 'Мб', 'Гб', 'Тб'][i]}`
  )
}

async function onFileSelected(e: Event) {
  const target = e.target as HTMLInputElement
  if (!target.files?.length) {
    return
  }
  const formData = new FormData()
  const file = target.files[0]
  formData.append('file', file)
  formData.append('folder', folder)
  const newFile = reactive<UploadedFile>({
    name: file.name,
    size: file.size,
  })
  if (Array.isArray(model.value)) {
    model.value.push(newFile)
  }
  else {
    model.value = newFile
  }
  const { data } = await useHttp<string>(
      `common/files`,
      {
        method: 'post',
        body: formData,
      },
  )
  if (data.value) {
    newFile.url = data.value
  }
}
</script>

<template>
  <div class="file-uploader">
    <template v-if="model !== null">
      <v-menu
        v-for="file in Array.isArray(model) ? model : [model]"
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
            <span class="text-gray">
              ({{ humanFileSize(file) }})
            </span>
          </v-list-item>
          <v-list-item @click="removeFile(file)">
            <template #prepend>
              <v-icon icon="$delete" />
            </template>
            удалить
          </v-list-item>
        </v-list>
      </v-menu>
    </template>
    <div v-if="model === null || Array.isArray(model)" class="mt-2">
      <UiIconLink icon="$file" prepend @click="selectFile()">
        прикрепить файл
      </UiIconLink>
    </div>
    <input
      ref="fileInput"
      style="display: none"
      type="file"
      :accept="folder === 'tests' ? 'application/pdf' : undefined"
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
