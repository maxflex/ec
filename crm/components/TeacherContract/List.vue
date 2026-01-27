<script setup lang="ts">
import type { TeacherContractListResource } from '.'
import { mdiAlertBox, mdiFilePdfBox } from '@mdi/js'
import { apiUrl } from '.'

const { items, highlightActive } = defineProps<{
  highlightActive?: boolean
  items: TeacherContractListResource[]
}>()

const emit = defineEmits<{
  edit: [id: number]
}>()
</script>

<template>
  <Table>
    <TableRow
      v-for="item in items" :id="`${apiUrl}-${item.id}`" :key="item.id" :class="{
        'teacher-contract--active': highlightActive && item.is_active,
        'teacher-contract--inactive': highlightActive && !item.is_active,
      }"
    >
      <TableCol :width="200">
        <UiPerson :item="item.teacher" />
      </TableCol>
      <TableCol :width="100">
        версия {{ item.seq }}
      </TableCol>
      <TableCol :width="90">
        {{ formatDate(item.date) }}
      </TableCol>
      <TableCol :width="90">
        {{ plural(item.total.groups, ['группа', 'группы', 'групп']) }}
      </TableCol>
      <TableCol :width="110">
        {{ plural(item.total.lessons, ['занятие', 'занятия', 'занятий']) }}
      </TableCol>
      <TableCol :width="110">
        {{ formatPrice(item.total.price) }}  руб.
      </TableCol>
      <TableCol :width="180">
        <template v-if="item.date_from">
          с {{ formatDate(item.date_from) }}
        </template>
        <template v-if="item.date_to">
          по {{ formatDate(item.date_to) }}
        </template>
      </TableCol>
      <TableCol :width="60">
        <span v-if="item.problems_count > 0" class="text-error d-flex align-center ga-1">
          <v-icon color="error" :icon="mdiAlertBox" />
          {{ item.problems_count }}
        </span>
      </TableCol>
      <TableCol>
        <div class="teacher-contract__buttons">
          <div>
            <a v-if="item.file" class="gray-link" target="_blank" :href="item.file.url">
              <v-icon :icon="mdiFilePdfBox" />
            </a>
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
.teacher-contract {
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

  &--active {
    // background: rgba(var(--v-theme-primary), 0.3);
  }
  &--inactive {
    & > div {
      opacity: 0.5;
    }
  }
}
</style>
