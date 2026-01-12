<script setup lang="ts">
import type { ClientReviewListResource } from '.'
import { apiUrl } from '.'

const { items, showClient } = defineProps<{
  items: ClientReviewListResource[]
  showClient?: boolean
}>()

const emit = defineEmits<{
  edit: [id: number]
}>()
</script>

<template>
  <Table>
    <TableRow v-for="item in items" :id="`${apiUrl}-${item.id}`" :key="item.id">
      <div class="table-actionss">
        <v-btn
          variant="plain"
          icon="$edit"
          :size="48"
          @click="emit('edit', item.id)"
        />
      </div>
      <TableCol v-if="showClient" :width="160">
        <UiPerson :item="item.client" />
      </TableCol>
      <TableCol :width="180">
        <UiPerson :item="item.teacher" />
      </TableCol>
      <TableCol :width="100">
        {{ ProgramShortLabel[item.program] }}
      </TableCol>
      <TableCol :width="110">
        <UiRating v-model="item.rating" />
      </TableCol>
      <TableCol class="text-truncate pr-2">
        {{ item.text }}
      </TableCol>
      <TableCol style="width: 130px; flex: initial" class="text-gray">
        {{ formatDateTime(item.created_at) }}
      </TableCol>
    </TableRow>
  </Table>
</template>
