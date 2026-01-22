<script setup lang="ts">
import type { TeacherComplaintResource } from '.'
import { apiUrl, TeacherComplaintRecipientLabel, TeacherComplaintStatusLabel } from '.'

const { items } = defineProps<{
  items: TeacherComplaintResource[]
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
            <div class="teacher-complaint__status" :class="`teacher-complaint__status--${item.status}`" v-bind="props" />
          </template>
          {{ TeacherComplaintStatusLabel[item.status] }}
        </v-tooltip>
      </TableCol>
      <TableCol :width="150">
        <UiPerson :item="item.teacher" />
      </TableCol>
      <TableCol :width="150">
        {{ formatDateTime(item.created_at) }}
      </TableCol>
      <TableCol :width="200">
        <span v-if="item.recipient">
          {{ TeacherComplaintRecipientLabel[item.recipient] }}
        </span>
        <span v-else class="text-gray">
          не установлено
        </span>
      </TableCol>
      <TableCol>
        {{ item.text }}
      </TableCol>
      <TableCol :width="100">
        <div class="teacher-complaint__buttons">
          <div class="teacher-complaint__comment">
            <CommentBtn
              :size="42"
              :class="{ 'no-items': item.comments_count === 0 }"
              :count="item.comments_count"
              :entity-id="item.id"
              :entity-type="EntityTypeValue.teacherComplaint"
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
.teacher-complaint {
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
    &--new {
      --background: rgb(var(--v-theme-error));
    }
    &--inProgress {
      --background: rgb(var(--v-theme-orange));
    }
    &--closed {
      --background: rgba(var(--v-theme-success));
    }
  }

  &__buttons {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    text-align: center;
    gap: 6px;
    & > div {
      display: inline-block;
      width: 42px;
      min-width: 42px;
    }
  }
}
</style>
