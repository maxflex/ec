<script setup lang="ts">
import { mdiDownload } from '@mdi/js'

const { folder, label = 'прикрепить файл' } = defineProps<{
  label?: string
  folder: 'lessons' | 'tests' | 'events'
}>()

const accept = (function () {
  switch (folder) {
    case 'tests':
      return 'application/pdf'

    case 'events':
      return 'image/*'

    default:
      return undefined
  }
})()

const fileInput = ref<HTMLInputElement>()

const model = defineModel<null | UploadedFile | UploadedFile[]>({ required: true })

function selectFile() {
  fileInput.value?.click()
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

/**
 * Ensures the filename is correctly encoded in UTF-8
 */
function fixFileNameEncoding(name: string): string {
  try {
    // Convert to bytes and back to UTF-8 to fix potential misinterpretation
    const bytes = new TextEncoder().encode(name)
    const decoded = new TextDecoder('utf-8').decode(bytes)

    // Normalize Unicode to NFC (fixes issues with different encodings of diacritics)
    return decoded.normalize('NFC')
  }
  catch {
    return name // Fallback in case of an error
  }
}

function onFileSelected(e: Event) {
  const target = e.target as HTMLInputElement
  if (!target.files?.length) {
    return
  }

  const files = Array.from(target.files)

  for (const file of files) {
    const formData = new FormData()
    formData.append('file', file)
    formData.append('folder', folder)

    const newFile = reactive<UploadedFile>({
      name: fixFileNameEncoding(file.name),
      size: file.size,
      url: '',
    })

    if (Array.isArray(model.value)) {
      model.value.push(newFile)
    }
    else {
      model.value = newFile
    }

    useHttp<string>(
      `upload/files`,
      {
        method: 'post',
        body: formData,
        onResponse: ({ response }) => {
          if (response.ok) {
            newFile.url = response._data
          }
          else {
            useGlobalMessage(`Файл ${newFile.name} слишком большой (${formatFileSize(newFile)}). Максимально допустимый размер 20 Мб`, 'error')
            removeFile(newFile)
          }
        },
      },
    )
  }

  // Reset input value to allow re-uploading the same file
  target.value = ''
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
              ({{ formatFileSize(file) }})
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
        {{ label }}
      </UiIconLink>
    </div>
    <input
      ref="fileInput"
      style="display: none"
      type="file"
      :multiple="Array.isArray(model)"
      :accept="accept"
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
