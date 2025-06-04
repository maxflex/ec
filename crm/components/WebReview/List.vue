<script setup lang="ts">
import type { WebReviewResource } from '.'
import { mdiAccount, mdiAccountCircleOutline, mdiWeb } from '@mdi/js'

const { items } = defineProps<{ items: WebReviewResource[] }>()
defineEmits<{
  edit: [itemId: number]
}>()
</script>

<template>
  <div class="table table--padding">
    <div
      v-for="item in items"
      :id="`web-review-${item.id}`"
      :key="item.id"
    >
      <UiTableActions>
        <v-btn
          icon="$edit"
          :size="48"
          variant="plain"
          @click="$emit('edit', item.id)"
        />
</UiTableActions>
      <div style="width: 50px">
        {{ item.id }}
      </div>
      <div
        v-if="item.client"
        style="width: 200px"
      >
        <NuxtLink :to="{ name: 'clients-id', params: { id: item.client.id } }">
          {{ formatName(item.client) }}
        </NuxtLink>
      </div>
      <div
        style="flex: 1"
        class="text-truncate"
      >
        {{ item.text }}
      </div>
      <div
        style="width: 150px"
        class="text-truncate"
      >
        {{ item.signature }}
      </div>

      <div style="width: 250px" class="d-flex flex-column">
        <span
          v-for="es in item.exam_scores"
          :key="es.id"
          :class="{ 'text-gray': !es.is_published }"
        >
          {{ ExamLabel[es.exam] }}: {{ es.score }}
        </span>
      </div>

      <div style="width: 60px" class="d-flex align-center ga-2">
        <v-icon :icon="mdiWeb" :class="item.is_published ? 'text-secondary' : 'opacity-2 text-gray'" />
        <v-icon :icon="mdiAccountCircleOutline" :class="item.has_photo ? 'text-secondary' : 'opacity-2 text-gray'" />
      </div>
      <div
        style="width: 100px; flex: initial !important"
      >
        <UiRating v-model="item.rating" />
      </div>
    </div>
  </div>
</template>
