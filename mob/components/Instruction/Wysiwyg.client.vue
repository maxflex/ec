<script setup lang="ts">
import { QuillEditor } from '@vueup/vue-quill'
import BlotFormatter from 'quill-blot-formatter/dist/BlotFormatter'
import ImageUploader from 'quill-image-uploader'
import 'quill-image-uploader/dist/quill.imageUploader.min.css'

const text = defineModel<string>()

const modules = [
  {
    name: 'imageUploader',
    module: ImageUploader,
    options: {
      upload: async (photo: Blob) => {
        const formData = new FormData()
        formData.append('photo', photo)
        const { data } = await useHttp<{ name: string }>(
          `photos/upload`,
          {
            method: 'post',
            body: formData,
          },
        )
        if (data.value) {
          return data.value.name
        }
      },
    },
  },
  {
    name: 'blotFormatter',
    module: BlotFormatter,
  },
]
</script>

<template>
  <QuillEditor
    v-model:content="text"
    theme="snow"
    content-type="html"
    :modules="modules"
    :toolbar="[
      [{ header: 1 }, 'bold', 'italic', 'underline'],
      [{ list: 'bullet' }, { list: 'ordered' }],
    ]"
  />
</template>
