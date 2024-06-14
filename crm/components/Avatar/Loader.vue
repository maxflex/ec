<script setup lang="ts">
import { mdiCloudUpload } from '@mdi/js'
import VuePictureCropper, { cropper } from 'vue-picture-cropper'

const { entity, item, size } = withDefaults(
  defineProps<{
    entity: Extract<EntityString, 'client' | 'teacher' | 'user'>
    item: PersonResource & HasPhoto
    size?: number
  }>(),
  {
    size: 150,
  },
)

const { dialog, width, transition } = useDialog('medium')
const saving = ref(false)
const uploadInput = ref<HTMLInputElement | null>(null)
const pic = ref<string>('')
const result = reactive({
  dataURL: '',
  blobURL: '',
})

function selectFile(e: Event) {
  // Reset last selection and results
  pic.value = ''
  result.dataURL = ''
  result.blobURL = ''

  // Get selected files
  const { files } = e.target as HTMLInputElement
  if (!files || !files.length) return

  // Convert to dataURL and pass to the cropper component
  const file = files[0]
  const reader = new FileReader()
  reader.readAsDataURL(file)
  reader.onload = () => {
    // Update the picture source of the `img` prop
    pic.value = String(reader.result)

    // Show the modal
    dialog.value = true

    // Clear selected files of input element
    if (!uploadInput.value) return
    uploadInput.value.value = ''
  }
}

async function save() {
  saving.value = true
  if (!cropper) return
  // const base64 = cropper.getDataURL()
  const blob: Blob | null = await cropper.getBlob()
  if (!blob) return
  console.log({ blob })
  const formData = new FormData()
  formData.append('photo', blob)
  formData.append('entity_type', EntityType[entity])
  formData.append('entity_id', String(item.id))
  const { data, error } = await useHttp<HasPhoto>('photos', {
    method: 'post',
    body: formData,
  })
  if (data.value) {
    console.log('UPLOADED', data.value)
    // eslint-disable-next-line
    item.photo_url = data.value.photo_url
  }
  if (error.value) {
    console.log('error', error.value)
  }
  dialog.value = false
  setTimeout(() => saving.value = false)
}
</script>

<template>
  <div
    class="avatar-loader"
    @click="uploadInput?.click()"
  >
    <UiAvatar
      :item="item"
      :size="size"
    />
    <div class="avatar-loader__overlay">
      <v-icon :icon="mdiCloudUpload" />
    </div>
  </div>
  <input
    ref="uploadInput"
    style="display: none;"
    type="file"
    accept="image/jpg, image/jpeg, image/png, image/gif"
    @change="selectFile"
  >
  <v-dialog
    v-model="dialog"
    :width="width"
    :transition="transition"
  >
    <div class="dialog-wrapper">
      <div class="dialog-header">
        Выберите область
        <v-btn
          icon="$save"
          :size="48"
          color="#fafafa"
          :loading="saving"
          @click="save()"
        />
      </div>
      <div class="dialog-body">
        <VuePictureCropper
          :box-style="{
            width: '100%',
            height: '100%',
            backgroundColor: '#f8f8f8',
            margin: 'auto',
          }"
          :img="pic"
          :options="{
            viewMode: 1,
            aspectRatio: 1,
            dragMode: 'crop',
          }"
        />
      </div>
    </div>
  </v-dialog>
</template>

<style lang="scss">
.avatar-loader {
  $transition: ease-in-out 0.2s;
  width: fit-content;
  margin: 0 auto;
  border-radius: 50%;
  position: relative;
  overflow: hidden;
  cursor: pointer;
  &__overlay {
    opacity: 0;
    transition: opacity $transition;
    background: linear-gradient(to top, black, transparent);
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: flex-end;
    justify-content: center;
    .v-icon {
      font-size: 35px;
      color: white;
      margin-bottom: 20px;
      transform: translateY(20px);
      transition: transform $transition;
    }
    &:hover {
      opacity: 1;
      .v-icon {
        transform: translateY(0);
      }
    }
  }
}
.cropper-bg {
  background-image: none !important;
}
.cropper-view-box,
.cropper-face {
  border-radius: 50%;
}

.cropper-view-box {
  outline: 0 !important;
  // box-shadow: 0 0 0 1px #39f;
}
.cropper-center,
.cropper-dashed {
  display: none !important;
}
.cropper-line,
.cropper-point {
  background-color: white !important;
  // background-color: $primary;
}
</style>
