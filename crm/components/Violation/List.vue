<script setup lang="ts">
import type { ViolationListResource } from '.'
import { mdiCamera, mdiVideo } from '@mdi/js'
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
      <TableCol :width="200">
        <span class="mr-6">
          {{ formatDate(item.lesson.date) }}
        </span>
        {{ formatTime(item.lesson.time) }} – {{ formatTime(item.lesson.time_end) }}
      </TableCol>
      <TableCol :width="100">
        <GroupLink :item="item.lesson.group" />
      </TableCol>
      <TableCol :width="140">
        {{ ProgramShortLabel[item.lesson.group.program] }}
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
      <TableCol :width="30">
        <a v-if="item.photo" class="gray-link" target="_blank" :href="item.photo.url">
          <v-icon :icon="mdiCamera" />
        </a>
      </TableCol>
      <TableCol :width="20">
        <a v-if="item.video" class="gray-link" target="_blank" :href="item.video.url">
          <v-icon :icon="mdiVideo" />
        </a>
      </TableCol>
      <TableCol>
        <div class="violation__comment">
          <CommentBtn
            :size="42"
            :class="{ 'no-items': item.comments_count === 0 }"
            :count="item.comments_count"
            :entity-id="item.id"
            :entity-type="EntityTypeValue.violation"
          />
        </div>
      </TableCol>
    </TableRow>
  </Table>
</template>

<style lang="scss">
.violation {
  &__comment {
    display: flex;
    align-items: center;
    width: 44px;
    color: rgb(var(--v-theme-gray));
    gap: 10px;
  }
}
</style>
