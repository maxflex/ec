<script setup lang="ts">
import { mdiWeb } from '@mdi/js'

const { items } = defineProps<{ items: ExamScoreResource[] }>()
const emit = defineEmits<{
  edit: [e: ExamScoreResource]
}>()
</script>

<template>
  <Table>
    <TableRow v-for="item in items" :id="`exam-score-${item.id}`" :key="item.id">
      <div class="table-actionss">
        <v-btn
          icon="$edit"
          :size="48"
          variant="plain"
          @click="emit('edit', item)"
        />
      </div>
      <TableCol :width="200">
        <NuxtLink :to="{ name: 'clients-id', params: { id: item.client!.id } }">
          {{ formatName(item.client!) }}
        </NuxtLink>
      </TableCol>
      <TableCol :width="220">
        {{ ExamLabel[item.exam!] }}
      </TableCol>
      <TableCol :width="200">
        {{ YearLabel[item.year] }}
      </TableCol>
      <TableCol :width="150">
        балл: {{ item.score }}
      </TableCol>
      <TableCol>
        <v-icon :icon="mdiWeb" :class="item.is_published ? 'text-secondary' : 'opacity-2 text-gray'" />
      </TableCol>
    </TableRow>
  </Table>
</template>
