<script lang="ts" setup>
import type { Tests } from '~/utils/models'

const emit = defineEmits<{
  (e: 'saved', tests: Tests): void
}>()
const { dialog, width } = useDialog('large')
const selected = ref<Tests>([])
const tests = ref<Tests>()

function open() {
  dialog.value = true
  loadData()
}

async function loadData() {
  if (tests.value !== undefined) {
    return
  }
  const { data } = await useHttp<ApiResponse<Tests>>('tests')
  if (data.value) {
    const { data: newItems } = data.value
    tests.value = newItems
  }
}

function save() {
  dialog.value = false
  emit('saved', selected.value)
}

defineExpose({ open })
</script>

<template>
  <v-dialog
    v-model="dialog"
    :width="width"
  >
    <div class="dialog-header">
      <span>
        Добавить тесты
        <span
          v-if="selected.length"
          class="ml-1 text-gray"
        >
          {{ selected.length }}
        </span>
      </span>
      <v-btn
        icon="$save"
        :size="48"
        color="#fafafa"
        @click="save()"
      />
    </div>
    <div class="dialog-body pt-0">
      <TestList
        v-if="tests"
        v-model:selected="selected"
        :tests="tests"
      />
    </div>
  </v-dialog>
</template>
