<script setup lang="ts">
import type { UploadFolder } from '.'
import { mdiDownload } from '@mdi/js'
import { getIcon } from '.'

const { folder, inline, label = 'прикрепить файл' } = defineProps<{
  label?: string
  inline?: boolean
  folder: UploadFolder
}>()

const accept = (function () {
  switch (folder) {
    case 'teacher-contracts':
    case 'tests':
      return 'application/pdf'

    case 'events':
    case 'violations':
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
      <template v-if="inline">
        <div
          v-for="file in Array.isArray(model) ? model : [model]"
          :key="file.url || file.name"
          class="file-uploader__inline"
          :class="{ 'opacity-4 no-pointer-events': !file.url }"
        >
          <v-icon :icon="getIcon(file).icon" :color="getIcon(file).color" />
          <div>
            <div class="file-uploader__inline-link">
              <a :href="file.url" target="_blank" class="text-truncate black-link">
                {{ file.name }}
              </a>
              <span class="text-gray">
                ({{ formatFileSize(file) }})
              </span>
            </div>
            <div class="file-uploader__inline-remove">
              <span @click="removeFile(file)">удалить файл</span>
            </div>
          </div>
        </div>
      </template>
      <template v-else>
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
  gap: 20px;

  &__inline {
    display: flex;
    gap: 6px;
    overflow: hidden;
    left: -4px;
    position: relative;

    .v-icon {
      font-size: 54px;
      top: -6px;
      position: relative;
    }

    & > div {
      line-height: 20px;
    }

    &-link {
      display: flex;
      align-items: baseline;
      gap: 6px;

      a {
        display: inline-block;
        max-width: 500px;
      }
    }

    &-remove {
      span {
        color: rgb(var(--v-theme-gray));
        font-size: 14px;
        cursor: pointer;
        &:hover {
          color: rgb(var(--v-theme-error));
        }
      }
    }
  }
}
</style>
