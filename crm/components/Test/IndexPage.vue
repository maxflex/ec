<script setup lang="ts">
import type { TestDialog } from '#build/components'
import type { TestResource } from '~/components/Test'

const testDialog = ref<InstanceType<typeof TestDialog>>()
const tests = ref<TestResource[]>()
const { isAdmin } = useAuthStore()

async function loadData() {
  const { data } = await useHttp<ApiResponse<TestResource>>(`tests`)
  if (data.value) {
    const { data: newItems } = data.value
    tests.value = newItems
  }
}

nextTick(loadData)
</script>

<template>
  <div class="tests">
    <UiTopPanel v-if="isAdmin">
      <v-spacer />
      <v-btn
        append-icon="$next"
        color="primary"
        @click="testDialog?.create()"
      >
        добавить тест
      </v-btn>
    </UiTopPanel>
    <TestList
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
