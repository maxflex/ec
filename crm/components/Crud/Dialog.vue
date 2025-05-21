<script setup lang="ts">
import type { CrudDialogData } from '.'

const props = defineProps<{
  data: CrudDialogData
}>()

const { width, saving, save, isEditing } = props.data

const model = defineModel<boolean>({ required: true })
</script>

<template>
  <v-dialog v-model="model" :width="width">
    <div class="dialog-wrapper">
      <div class="dialog-header">
        <slot v-if="isEditing" name="title-edit">
          Редактирование записи
        </slot>
        <slot v-else name="title-create">
          Добавить запись
        </slot>
        <v-btn icon="$save" variant="text" :size="48" :loading="saving" @click="save()" />
      </div>
      <div class="dialog-body">
        <slot />
      </div>
    </div>
  </v-dialog>
</template>
