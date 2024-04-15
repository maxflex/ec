<script setup lang="ts">
import type { TestDialog } from "#build/components"
import type { Tests } from "~/utils/models"

const testDialog = ref<null | InstanceType<typeof TestDialog>>()
const tests = ref<Tests>()

async function loadData() {
  const { data } = await useHttp<ApiResponse<Tests>>("tests")
  if (data.value) {
    const { data: newItems } = data.value
    tests.value = newItems
  }
}

nextTick(loadData)
</script>

<template>
  <div class="tests">
    <UiTopPanel>
      <v-spacer />
      <v-btn append-icon="$next" @click="testDialog?.create()" color="primary">
        добавить тест
      </v-btn>
    </UiTopPanel>
    <TestList v-if="tests" :tests="tests" @open="testDialog?.open" />
  </div>
  <TestDialog ref="testDialog" @updated="loadData()" />
</template>
