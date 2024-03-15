<script setup lang="ts">
import type { TestDialog } from "#build/components"

definePageMeta({ middleware: ["check-active-test"] })

const testDialog = ref<null | InstanceType<typeof TestDialog>>()
const tests = ref<ClientTest[]>()

onMounted(async () => {
  await loadData()
})

async function loadData() {
  const { data } = await useHttp<ApiResponse<ClientTest[]>>("client/tests")
  if (data.value) {
    tests.value = data.value.data
  }
}
</script>

<template>
  <div class="tests">
    <TestClientList v-if="tests" :tests="tests" @open="testDialog?.open" />
  </div>
  <TestDialog ref="testDialog" @updated="loadData()" />
</template>
