<script setup lang="ts">
import type { ViolationListResource } from '.'
import { mdiImage, mdiVideo } from '@mdi/js'
import { apiUrl } from '.'

const { items } = defineProps<{
  items: ViolationListResource[]
}>()

const emit = defineEmits<{
  edit: [id: number]
}>()
</script>

<template>
  <Table>
    <TableRow v-for="item in items" :id="`${apiUrl}-${item.id}`" :key="item.id">
      <TableActions @click="emit('edit', item.id)" />
      <TableCol :width="150">
        Занятие от {{ formatDate(item.lesson.date) }}
      </TableCol>
      <TableCol :width="130">
        {{ formatTime(item.lesson.time) }} – {{ formatTime(item.lesson.time_end) }}
      </TableCol>
      <TableCol :width="160">
        <UiPerson :item="item.lesson.teacher" />
      </TableCol>
      <TableCol :width="190">
        <UiIfSet :value="!!item.client_lesson_id">
          <template #empty>
            ученик не установлен
          </template>
          <UiPerson :item="item.client!" />
        </UiIfSet>
      </TableCol>
      <TableCol :width="130">
        <span v-if="item.is_resolved" class="text-success">
          обработано
        </span>
        <span v-else class="text-gray">
          не обработано
        </span>
      </TableCol>
      <TableCol :width="20">
        <a v-if="item.file" class="black-link" target="_blank" :href="item.file.url">
          <v-icon :icon="mdiVideo" />
        </a>
      </TableCol>
      <TableCol>
        {{ item.comment }}
      </TableCol>
    </TableRow>
  </Table>
</template>
