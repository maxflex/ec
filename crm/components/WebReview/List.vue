<script setup lang="ts">
import { mdiNumeric5BoxMultiple, mdiWeb } from '@mdi/js'
import type { ExamScoreDialog } from '#components'

const { items } = defineProps<{ items: WebReviewResource[] }>()
defineEmits<{
  edit: [itemId: number]
}>()
const examScoreDialog = ref<InstanceType<typeof ExamScoreDialog>>()
</script>

<template>
  <div class="table">
    <div
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
        style="width: 200px"
        class="text-truncate"
      >
        {{ item.signature }}
      </div>
      <div
        style="flex: 1"
        class="text-truncate"
      >
        {{ item.text }}
      </div>
      <div
        style="width: 100px"
        class="text-center"
      >
        <v-icon
          :class="{ 'opacity-2': !item.is_published }"
          :icon="mdiWeb"
          :color="item.is_published ? 'secondary' : 'gray'"
          class="mr-3"
        />
        <v-icon
          v-if="item.exam_score_id"
          :icon="mdiNumeric5BoxMultiple"
          color="secondary"
          @click="examScoreDialog?.edit(item.exam_score_id)"
        />
        <v-icon
          v-else
          :icon="mdiNumeric5BoxMultiple"
          class="opacity-2"
          color="gray"
        />
      </div>
      <div
        style="width: 100px; flex: initial !important"
      >
        <UiRating v-model="item.rating" />
      </div>
    </div>
  </div>
  <ExamScoreDialog ref="examScoreDialog" />
</template>
