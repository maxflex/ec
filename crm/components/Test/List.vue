<script setup lang="ts">
import type { TestResource } from '.'

const { tests } = defineProps<{ tests: TestResource[] }>()
const emit = defineEmits<{
  open: [t: TestResource]
}>()
</script>

<template>
  <div class="table table--padding test-list">
    <div v-for="t in tests" :key="t.id">
      <UiTableActions>
        <v-btn
          variant="plain"
          color="gray"
          icon="$edit"
          :size="48"
          @click="emit('open', t)"
        />
</UiTableActions>
      <div style="width: 380px">
        {{ t.name }}
        <div v-if="t.description">
          {{ t.description }}
        </div>
      </div>

      <div style="width: 160px">
        {{ t.minutes }} минут
        <div>
          <span v-if="t.questions?.length">
            {{ plural(t.questions.length, ["вопрос", "вопроса", "вопросов"]) }}
          </span>
          <span v-else class="text-gray"> нет вопросов </span>
        </div>
      </div>
      <div style="width: 150px" class="font-weight-bold">
        {{ t.max_score }} баллов
      </div>
      <div>
        <FileItem v-if="t.file" :item="t.file" class="vf-1" style="top: 3px; width: 400px" />
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.test-list {
  .table-actionss {
    align-items: center;
    display: flex;
    justify-content: flex-end;
    padding: 0 !important;
  }
}
</style>
