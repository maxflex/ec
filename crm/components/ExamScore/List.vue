<script setup lang="ts">
import { WebReviewDialog } from '#components'

const { items } = defineProps<{ items: ExamScoreResource[] }>()
const emit = defineEmits<{
  edit: [e: ExamScoreResource]
}>()
const webReviewDialog = ref<InstanceType<typeof WebReviewDialog>>()
</script>

<template>
  <div class="table">
    <div v-for="e in items" :id="`exam-score-${e.id}`" :key="e.id">
      <div class="table-actionss">
        <v-btn
          icon="$edit"
          :size="48"
          variant="plain"
          @click="emit('edit', e)"
        />
      </div>
      <div style="width: 200px">
        <NuxtLink :to="{ name: 'clients-id', params: { id: e.client!.id } }">
          {{ formatName(e.client!) }}
        </NuxtLink>
      </div>
      <div style="width: 220px">
        {{ ExamLabel[e.exam!] }}
      </div>
      <div style="width: 200px">
        {{ YearLabel[e.year] }}
      </div>
      <div style="width: 140px">
        балл: {{ e.score }}
      </div>
      <div>
        <a v-if="e.web_review_id" class="cursor-pointer" @click="webReviewDialog?.edit(e.web_review_id)">
          отзыв {{ e.web_review_id }}
        </a>
      </div>
    </div>
  </div>
  <WebReviewDialog ref="webReviewDialog" />
</template>
