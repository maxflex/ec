<script setup lang="ts">
import type { RealReport } from '..'

const { items } = defineProps<{
  items: RealReport[]
}>()

const router = useRouter()
</script>

<template>
  <Table hoverable>
    <TableRow
      v-for="r in items"
      :id="`report-${r.id}`"
      :key="r.id"
      class="cursor-pointer"
      @click="router.push({ name: 'reports-id', params: { id: r.id } })"
    >
      <TableCol :width="170">
        <UiPerson :item="r.teacher" />
      </TableCol>
      <TableCol :width="140">
        {{ ProgramShortLabel[r.program] }}
      </TableCol>
      <TableCol :width="120">
        занятий: {{ r.lessons_count }}
      </TableCol>

      <TableCol :width="50">
        <span v-if="r.grade" :class="`text-score text-score--${r.grade}`">
          {{ r.grade }}
        </span>
      </TableCol>
      <TableCol style="width: 100px; flex: initial" class="text-gray">
        <span v-if="r.to_check_at">
          {{ formatTextDate(r.to_check_at, true) }}
        </span>
      </TableCol>
    </TableRow>
  </Table>
</template>
