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
      <TableCol :width="10">
        <v-tooltip>
          <template #activator="{ props }">
            <div class="violation__status" :class="`violation__status--${item.is_resolved ? 'resolved' : 'unresolved'}`" v-bind="props" />
          </template>
          {{ item.is_resolved ? 'нарушение обработано' : 'нарушение не обработано' }}
        </v-tooltip>
      </TableCol>
      <TableCol :width="70">
        {{ formatDate(item.lesson.date) }}
      </TableCol>
      <TableCol :width="120">
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
      <!-- <TableCol :width="130">
        <span v-if="item.is_resolved" class="text-success">
          обработано
        </span>
        <span v-else class="text-gray">
          не обработано
        </span>
      </TableCol> -->

      <TableCol>
        <div class="violation__buttons">
          <div>
            <a v-if="item.photo" class="gray-link" target="_blank" :href="item.photo.url">
              <v-icon :icon="mdiCamera" />
            </a>
          </div>
          <div>
            <a v-if="item.video" class="gray-link" target="_blank" :href="item.video.url">
              <v-icon :icon="mdiVideo" :size="28" />
            </a>
          </div>
          <div class="violation__comment">
            <CommentBtn
              :size="42"
              :class="{ 'no-items': item.comments_count === 0 }"
              :count="item.comments_count"
              :entity-id="item.id"
              :entity-type="EntityTypeValue.violation"
            />
          </div>
          <div>
            <v-btn
              variant="plain"
              color="gray"
              icon="$edit"
              :size="42"
              @click="emit('edit', item.id)"
            />
          </div>
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

  &__status {
    --size: 10px;
    height: var(--size);
    width: var(--size);
    background: var(--background);
    border-radius: 50%;
    position: relative;
    overflow: hidden;
    &--unresolved {
      --background: rgb(var(--v-theme-warning));
    }
    &--resolved {
      --background: rgba(var(--v-theme-success));
    }
  }

  &__buttons {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    text-align: center;
    gap: 4px;
    & > div {
      display: inline-block;
      width: 42px;
      min-width: 42px;
    }
  }
}
</style>
