<script setup lang="ts">
import type { Group, Groups, Program } from '~/utils/models'

const { dialog, width } = useDialog(1000)
const groups = ref<Groups>()
const emit = defineEmits<{
  (e: 'select', g: Group): void
}>()

function open(p: Program) {
  dialog.value = true
  loadGroups(p)
}

function select(g: Group) {
  dialog.value = false
  emit('select', g)
}

async function loadGroups(p: Program) {
  const { data } = await useHttp<ApiResponse<Groups>>('groups', {
    params: {
      program: p,
    },
  })
  if (data.value) {
    groups.value = data.value.data
  }
}

defineExpose({ open })
</script>

<template>
  <v-dialog
    v-model="dialog"
    :width="width"
  >
    <div class="dialog-wrapper">
      <div class="dialog-header">
        Присвоить группу
      </div>
      <div class="dialog-body pt-0">
        <v-fade-transition>
          <div
            v-if="!groups"
            class="dialog-loader"
          >
            <v-progress-circular
              :size="50"
              indeterminate
            />
          </div>
        </v-fade-transition>
        <div
          v-if="groups"
          class="table table--hover table--padding"
        >
          <GroupItem
            v-for="group in groups"
            :key="group.id"
            :group="group"
            selectable
            @select="() => select(group)"
          />
        </div>
      </div>
    </div>
  </v-dialog>
</template>
