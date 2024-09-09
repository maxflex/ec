<script setup lang="ts">
import { mdiFilePdfBox, mdiFileTableBox, mdiImage } from '@mdi/js'

const { item } = defineProps<{
  item: UploadedFile
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
</script>

<template>
  <div class="file-item" :class="{ 'opacity-disabled': !item.url }">
    <v-icon :icon="getIcon(item).icon" :color="getIcon(item).color" />
    <span>
      {{ filterTruncate(item.name, 40) }}
    </span>
  </div>
</template>

<style scoped lang="scss">
.file-item {
  display: inline-flex;
  align-items: center;
  position: relative;
  cursor: pointer;
  width: fit-content;
  left: -5px;
  &:not(:hover) {
    color: black;
  }
  .v-icon {
    font-size: 44px;
    margin-right: 8px;
  }
}
</style>
