<script lang="ts" setup>
import type { Tests } from "~/utils/models"

const { dialog, width } = useDialog(1000)
const selected = ref([])
const tests = ref<Tests>()

function open() {
  dialog.value = true
  loadData()
  // selected.value = [...preSelect]
}

async function loadData() {
  if (tests.value !== undefined) {
    return
  }
  const { data } = await useHttp<ApiResponse<Tests>>("tests")
  if (data.value) {
    const { data: newItems } = data.value
    tests.value = newItems
  }
}

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-header">
      <span>
        Выберите тесты
        <span class="ml-1 text-gray" v-if="selected.length">
          {{ selected.length }}
        </span>
      </span>
      <v-btn icon="$save" :size="48" color="#fafafa" />
    </div>
    <div class="dialog-body pt-0">
      <TestList v-if="tests" :tests="tests" selectable />
    </div>
  </v-dialog>
</template>
