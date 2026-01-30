<script setup lang="ts">
import type { TeacherActListResource } from '.'
import { mdiAlertBox, mdiFilePdfBox } from '@mdi/js'
import { apiUrl } from '.'

const { items } = defineProps<{
  items: TeacherActListResource[]
}>()

const emit = defineEmits<{
  edit: [id: number]
}>()
</script>

<template>
  <Table>
    <TableRow v-for="item in items" :id="`${apiUrl}-${item.id}`" :key="item.id">
      <TableCol :width="180">
        <UiPerson :item="item.teacher" />
      </TableCol>
      <TableCol :width="90">
        акт
      </TableCol>
      <TableCol :width="90">
        {{ formatDate(item.date) }}
      </TableCol>
      <TableCol :width="90">
        {{ plural(item.total.groups, ['группа', 'группы', 'групп']) }}
      </TableCol>
      <TableCol :width="120">
        {{ plural(item.total.lessons, ['занятие', 'занятия', 'занятий']) }}
      </TableCol>
      <TableCol :width="150">
        {{ formatPrice(item.total.price) }}  руб.
        <div class="text-gray text-caption">
          {{ formatPrice(item.total.price * 0.15) }} руб. НДФЛ
        </div>
      </TableCol>
      <TableCol :width="170">
        <template v-if="item.date_from">
          с {{ formatDate(item.date_from) }}
        </template>
        <template v-if="item.date_to">
          по {{ formatDate(item.date_to) }}
        </template>
      </TableCol>
      <TableCol>
        <div class="teacher-act__buttons">
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
.teacher-act {
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
