<script setup lang="ts">
import { mdiPlayCircle } from '@mdi/js'

const { folder, label = 'прикрепить видео' } = defineProps<{
  label?: string
  folder: UploadFolder
}>()

const fileInput = ref<HTMLInputElement>()

const model = defineModel<null | UploadedFile>({ required: true })

function selectFile() {
  fileInput.value?.click()
}

function removeFile() {
  model.value = null
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
            removeFile()
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
  <div class="video-uploader">
    <div v-if="model === null" class="mt-2">
      <UiIconLink icon="$file" prepend @click="selectFile()">
        {{ label }}
      </UiIconLink>
    </div>
    <div v-else class="video-uploader__preview">
      <a :href="model.url" target="_blank">
        <video
          :src="model.url"
          preload="metadata"
          muted
          playsinline
          class="video-thumb"
        />
        <v-icon :icon="mdiPlayCircle" />
      </a>
      <div class="text-truncate">
        <div class="text-gray text-truncate">
          {{ filterTruncate(model.name, 26) }}
          ({{ formatFileSize(model) }})
        </div>
        <div>
          <a class="text-error cursor-pointer" @click="removeFile()">удалить видео</a>
        </div>
      </div>
    </div>
    <input
      ref="fileInput"
      style="display: none"
      type="file"
      accept="video/*"
      @change="onFileSelected"
    >
  </div>
</template>

<style lang="scss">
.video-uploader {
  display: flex;
  flex-direction: column;
  gap: 10px;

  &__preview {
    // background-position: center;
    // background-size: cover;
    // height: 200px;
    // width: auto;
    // // width: 100%;
    // display: inline-block;
    // border-radius: 8px;
    display: flex;
    gap: 20px;
    overflow: hidden;

    & > a {
      min-width: 130px;
      width: 130px;
      min-height: 80px;
      height: 80px;
      border-radius: 8px;
      background: rgb(var(--v-theme-bg));
      display: inline-flex;
      align-items: center;
      justify-content: center;
      .v-icon {
        transition: all ease-in-out 0.2s;
        font-size: 44px;
        position: absolute;
        opacity: 0.8;
        color: white !important;
      }
      &:hover {
        .v-icon {
          font-size: 40px;
          opacity: 1;
        }
      }

      video {
        width: 130px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
        background: #000;
      }
    }

    & > div {
      flex: 1;
    }
  }
}
</style>
