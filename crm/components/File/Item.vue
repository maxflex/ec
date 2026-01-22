<script setup lang="ts">
import { getIcon } from '.'

const { item, downloadable } = defineProps<{
  item: UploadedFile
  downloadable?: boolean
  showSize?: boolean
}>()

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
