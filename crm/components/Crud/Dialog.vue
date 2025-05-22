<script setup lang="ts">
import type { CrudDialogData } from '~/composables/useCrud'

const props = defineProps<{
  data: CrudDialogData
}>()

const { width, saving, deleting, isEditing, save, destroy } = props.data

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
        <div>
          <v-btn
            v-if="isEditing"
            :loading="deleting"
            :size="48"
            class="remove-btn"
            icon="$delete"
            variant="text"
            @click="destroy()"
          />
          <v-btn icon="$save" variant="text" :size="48" :loading="saving" @click="save()" />
        </div>
      </div>
      <div class="dialog-body">
        <slot />
      </div>
    </div>
  </v-dialog>
</template>
