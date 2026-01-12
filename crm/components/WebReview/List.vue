<script setup lang="ts">
import type { WebReviewResource } from '.'
import { mdiAccountCircleOutline, mdiWeb } from '@mdi/js'

const { items } = defineProps<{ items: WebReviewResource[] }>()
defineEmits<{
  edit: [itemId: number]
}>()
</script>

<template>
  <Table class="table--padding">
    <TableRow
      v-for="item in items"
      :id="`web-review-${item.id}`"
      :key="item.id"
    >
      <div class="table-actionss">
        <v-btn
          icon="$edit"
          :size="48"
          variant="plain"
          @click="$emit('edit', item.id)"
        />
      </div>
      <TableCol :width="50">
        {{ item.id }}
      </TableCol>
      <TableCol
        v-if="item.client"
        :width="200"
      >
        <NuxtLink :to="{ name: 'clients-id', params: { id: item.client.id } }">
          {{ formatName(item.client) }}
        </NuxtLink>
      </TableCol>
      <TableCol
        class="text-truncate"
      >
        {{ item.text }}
      </TableCol>
      <TableCol
        :width="150"
        class="text-truncate"
      >
        {{ item.signature }}
      </TableCol>

      <TableCol :width="250" class="d-flex flex-column">
        <span
          v-for="es in item.exam_scores"
          :key="es.id"
          :class="{ 'text-gray': !es.is_published }"
        >
          {{ ExamLabel[es.exam] }}: {{ es.score }}
        </span>
      </TableCol>

      <TableCol :width="60" class="d-flex align-center ga-2">
        <v-icon :icon="mdiWeb" :class="item.is_published ? 'text-secondary' : 'opacity-2 text-gray'" />
        <v-icon :icon="mdiAccountCircleOutline" :class="item.has_photo ? 'text-secondary' : 'opacity-2 text-gray'" />
      </TableCol>
      <TableCol
        style="width: 100px; flex: initial !important"
      >
        <UiRating v-model="item.rating" />
      </TableCol>
    </TableRow>
  </Table>
</template>
