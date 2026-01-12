<script setup lang="ts">
import type { TestResource } from '.'

const { tests } = defineProps<{ tests: TestResource[] }>()
const emit = defineEmits<{
  open: [t: TestResource]
}>()
</script>

<template>
  <Table class="table--padding test-list">
    <TableRow v-for="t in tests" :key="t.id">
      <div class="table-actionss">
        <v-btn
          variant="plain"
          color="gray"
          icon="$edit"
          :size="48"
          @click="emit('open', t)"
        />
      </div>
      <TableCol :width="380">
        {{ t.name }}
        <div v-if="t.description">
          {{ t.description }}
        </div>
      </TableCol>

      <TableCol :width="160">
        {{ t.minutes }} минут
        <div>
          <span v-if="t.questions?.length">
            {{ plural(t.questions.length, ["вопрос", "вопроса", "вопросов"]) }}
          </span>
          <span v-else class="text-gray"> нет вопросов </span>
        </div>
      </TableCol>
      <TableCol :width="150" class="font-weight-bold">
        {{ t.max_score }} баллов
      </TableCol>
      <TableCol>
        <FileItem v-if="t.file" :item="t.file" class="vf-1" style="top: 3px; width: 400px" downloadable />
      </TableCol>
    </TableRow>
  </Table>
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
