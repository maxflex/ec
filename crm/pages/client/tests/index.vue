<script setup lang="ts">
import type { TestDialog } from '#build/components'

definePageMeta({ middleware: ['check-active-test'] })

const testDialog = ref<null | InstanceType<typeof TestDialog>>()
const tests = ref<ClientTest[]>()

async function loadData() {
  const { data } = await useHttp<ApiResponse<ClientTest[]>>('tests')
  if (data.value) {
    tests.value = data.value.data
  }
}

nextTick(loadData)
</script>

<template>
  <div class="tests">
    <ClientTestList
      v-if="tests"
      :tests="tests"
      @open="testDialog?.open"
    />
  </div>
  <TestDialog
    ref="testDialog"
    @updated="loadData()"
  />
</template>
