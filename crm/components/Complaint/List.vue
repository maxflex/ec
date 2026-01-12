<script setup lang="ts">
import type { ComplaintListResource } from '.'
import { apiUrl } from '.'

const { items, clientId, teacherId } = defineProps<{
  items: ComplaintListResource[]
  clientId?: number
  teacherId?: number
}>()

const emit = defineEmits<{
  edit: [id: number]
}>()
</script>

<template>
  <Table>
    <TableRow v-for="item in items" :id="`${apiUrl}-${item.id}`" :key="item.id" class="pr-2">
      <TableCol :width="20">
        <v-tooltip>
          <template #activator="{ props }">
            <div class="complaints__status" :class="`complaints__status--${item.is_resolved ? 'resolved' : 'unresolved'}`" v-bind="props" />
          </template>
          {{ item.is_resolved ? 'вопрос решен' : 'требует решения' }}
        </v-tooltip>
      </TableCol>
      <TableCol v-if="!clientId" :width="180" class="text-truncate">
        <UiPerson :item="item.client" />
      </TableCol>
      <TableCol v-if="!teacherId" :width="180" class="text-truncate">
        <UiPerson :item="item.teacher" />
      </TableCol>
      <TableCol :width="120">
        {{ ProgramShortLabel[item.program] }}
      </TableCol>
      <TableCol :width="80">
        {{ formatDate(item.created_at) }}
      </TableCol>
      <TableCol class="text-truncate pr-2">
        {{ item.text }}
      </TableCol>
      <TableCol class="complaints__comment">
        <CommentBtn
          :size="42"
          :class="{ 'no-items': item.comments_count === 0 }"
          :count="item.comments_count"
          :entity-id="item.id"
          :entity-type="EntityTypeValue.complaint"
        />
        <div class="vfn-1">
          <v-btn
            icon="$edit"
            :size="42"
            variant="plain"
            @click="emit('edit', item.id)"
          />
        </div>
      </TableCol>
    </TableRow>
  </Table>
</template>

<style lang="scss">
.complaints {
  &__comment {
    display: flex;
    align-items: center;
    flex: initial !important;
    width: 100px;
    right: 68px;
    top: 16px;
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
}
</style>
