<script setup lang="ts">
const emit = defineEmits<{
  select: [g: GroupListResource]
}>()
const { dialog, width } = useDialog('large')
const groups = ref<GroupListResource[]>()

function open(p: Program) {
  dialog.value = true
  loadGroups(p)
}

function onSelect(g: GroupListResource) {
  dialog.value = false
  emit('select', g)
}

async function loadGroups(p: Program) {
  const { data } = await useHttp<ApiResponse<GroupListResource[]>>('groups', {
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
          <GroupList
            :items="groups"
            selectable
            @select="onSelect"
          />
        </div>
      </div>
    </div>
  </v-dialog>
</template>
