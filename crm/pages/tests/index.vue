<script setup lang="ts">
import type { TestDialog } from "#build/components"
import type { Test, Tests } from "~/utils/models"
import { PROGRAM } from "~/utils/sment"

const testDialog = ref<null | InstanceType<typeof TestDialog>>()
const tests = ref<Tests>()

onMounted(async () => {
  await loadData()
})

async function loadData() {
  const { data } = await useHttp<ApiResponse<Tests>>("tests")
  if (data.value) {
    const { data: newItems } = data.value
    tests.value = newItems
  }
}
</script>

<template>
  <div class="tests">
    <UiTopPanel>
      <v-spacer />
      <v-btn
        append-icon="$right"
        color="secondary"
        @click="testDialog?.create()"
      >
        добавить тест
      </v-btn>
    </UiTopPanel>
    <div class="table table--hover">
      <div v-for="t in tests" :key="t.id">
        <div style="width: 200px">
          <NuxtLink :to="{ name: 'tests-id', params: { id: t.id } }">
            {{ t.name }}
          </NuxtLink>
        </div>
        <div style="width: 300px">
          {{ PROGRAM[t.program] }}
        </div>
        <div>{{ t.minutes }} минут</div>
        <div class="table-actions">
          <v-btn
            variant="text"
            icon="$more"
            :size="48"
            @click="() => testDialog?.open(t)"
          />
        </div>
      </div>
    </div>
  </div>
  <TestDialog ref="testDialog" @added="loadData()" />
</template>
