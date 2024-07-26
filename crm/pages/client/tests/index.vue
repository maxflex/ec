<script setup lang="ts">
import type { TestDialog } from '#build/components'

definePageMeta({ middleware: ['check-active-test'] })

const testDialog = ref<InstanceType<typeof TestDialog>>()
const items = ref<ClientTestResource[]>()

async function loadData() {
  const { data } = await useHttp<ApiResponse<ClientTestResource[]>>(`client-tests`)
  if (data.value) {
    items.value = data.value.data
  }
}

nextTick(loadData)
</script>

<template>
  <div class="tests">
    <ClientTestList
      v-if="items"
      :items="items"
      @open="testDialog?.open"
    />
  </div>
  <TestDialog
    ref="testDialog"
    @updated="loadData()"
  />
</template>
