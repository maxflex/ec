<script setup lang="ts">
import { mdiFilePdfBox, mdiFilePowerpoint, mdiFileTableBox, mdiImage } from '@mdi/js'

const { item, downloadable } = defineProps<{
  item: UploadedFile
  downloadable?: boolean
  showSize?: boolean
}>()

function getIcon(file: UploadedFile): UploadedFileIcon {
  const index = file.name.lastIndexOf('.')
  const extension = file.name.slice(index + 1)
  switch (extension) {
    case 'pdf':
      return {
        icon: mdiFilePdfBox,
        color: '#e75a5a',
      }
    case 'ppt':
      return {
        icon: mdiFilePowerpoint,
        color: '#cb4f2e',
      }
    case 'gif':
    case 'svg':
    case 'png':
    case 'jpg':
    case 'jpeg':
      return {
        icon: mdiImage,
        color: '#BEAC83',
      }
    default:
      return {
        icon: mdiFileTableBox,
        color: '#6caf57',
      }
  }
}

function onClick() {
  if (downloadable) {
    window.open(item.url)
  }
}

const uploading = computed(() => !item.url)
</script>

<template>
  <div
    class="file-item"
    :class="{
      'file-item--uploading': uploading,
    }"
    @click="onClick()"
  >
    <v-icon :icon="getIcon(item).icon" :color="getIcon(item).color" />
    <span class="file-item__name text-truncate">
      {{ item.name }}
    </span>
    <span v-if="showSize" class="ml-2 text-gray">
      {{ formatFileSize(item) }}
    </span>
    <UiLoader v-if="uploading" />
  </div>
</template>

<style lang="scss">
.file-item {
  display: inline-flex;
  align-items: center;
  position: relative;
  cursor: pointer;
  left: -5px;
  &:not(:hover) {
    color: black;
  }
  .v-icon {
    font-size: 44px;
    margin-right: 8px;
  }
  .loader {
    width: fit-content;
    right: 0;
  }
  &__name {
    flex: 1;
  }
  &--uploading {
    & > *:not(.loader) {
      opacity: 0.4;
    }
  }
  // &--fit-content {
  //   width: fit-content;
  // }
}
</style>
