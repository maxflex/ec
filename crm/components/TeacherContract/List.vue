<script setup lang="ts">
import type { TeacherContractListResource } from '.'
import { uniqBy } from 'lodash-es'
import { apiUrl } from '.'

const { items } = defineProps<{
  items: TeacherContractListResource[]
}>()

const emit = defineEmits<{
  edit: [id: number]
}>()
</script>

<template>
  <Table>
    <TableRow v-for="item in items" :id="`${apiUrl}-${item.id}`" :key="item.id">
      <TableCol :width="200">
        <UiPerson :item="item.teacher" />
      </TableCol>
      <TableCol :width="140">
        версия {{ item.seq }}
      </TableCol>
      <TableCol :width="130">
        {{ formatDate(item.date) }}
      </TableCol>
      <TableCol :width="150">
        {{ plural(uniqBy(item.data, 'group_id').length, ['группа', 'группы', 'групп']) }}
      </TableCol>
      <TableCol :width="150">
        {{ plural(item.data.reduce((carry, x) => carry + x.cnt, 0), ['занятие', 'занятия', 'занятий']) }}
      </TableCol>
      <TableCol :width="150">
        {{ formatPrice(item.data.reduce((carry, x) => carry + (x.cnt * x.price), 0)) }}  руб.
      </TableCol>
      <TableActions @click="emit('edit', item.id)" />
    </TableRow>
  </Table>
</template>
