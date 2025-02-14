<script setup lang="ts">
import type { ClientReviewDialog } from '#build/components'
import { mdiNumeric5Circle, mdiTextBoxOutline, mdiWebPlus } from '@mdi/js'

const props = defineProps<{
  items: ClientReviewListResource[]
}>()
const { items } = toRefs(props)
const clientReviewDialog = ref<InstanceType<typeof ClientReviewDialog>>()
const { isTeacher } = useAuthStore()

function isRealClientReview(r: ClientReviewListResource): r is RealClientReview {
  return 'rating' in r
}

function onUpdated(r: RealClientReview) {
  const index = items.value.findIndex(e => e.id === r.id)
  if (index === -1) {
    return
  }
  items.value[index] = r
  itemUpdated('clientReview', r.id)
}

function onCreated(r: RealClientReview, fakeItemId: string) {
  const index = items.value.findIndex(e => e.id === fakeItemId)
  if (index === -1) {
    return
  }
  items.value[index] = r
  itemUpdated('clientReview', r.id)
}

function onDeleted(r: ClientReviewResource) {
  const index = items.value.findIndex(e => e.id === r.id)
  if (index !== -1) {
    items.value.splice(index, 1)
  }
}
</script>

<template>
  <div class="table">
    <div v-for="r in items" :id="`client-review-${r.id}`" :key="r.id">
      <div v-if="!isTeacher" style="width: 150px">
        <UiPerson :item="r.teacher" />
      </div>
      <div style="width: 170px">
        <UiPerson :item="r.client" />
      </div>
      <div style="width: 100px">
        {{ ProgramShortLabel[r.program] }}
      </div>
      <div style="width: 100px">
        занятий: {{ r.lessons_count }}
      </div>
      <div style="width: 140px">
        <div v-for="year in r.years" :key="year">
          {{ YearLabel[year] }}
        </div>
      </div>
      <template v-if="isRealClientReview(r)">
        <div class="table-actionss">
          <v-btn
            icon="$edit"
            :size="48"
            variant="plain"
            @click="clientReviewDialog?.edit(r.id)"
          />
        </div>
        <div style="flex: 1">
          {{ filterTruncate(r.text, 10) }}
        </div>
        <div class="text-gray" style="width: 100px">
          {{ formatDate(r.created_at) }}
        </div>
        <div style="width: 30px">
          <v-icon v-if="r.is_web_review_create" :icon="mdiWebPlus" class="text-gray" />
        </div>
        <div style="width: 30px">
          <v-icon v-if="r.has_exam_scores" :icon="mdiNumeric5Circle" class="text-gray" />
        </div>
        <div style="width: 106px; flex: initial" class="d-flex">
          <UiRating v-model="r.rating" />
        </div>
      </template>
      <template v-else>
        <div class="table-actionss">
          <v-btn
            icon="$edit"
            :size="48"
            variant="plain"
            @click="clientReviewDialog?.create(r)"
          />
        </div>
        <div class="text-right">
          <span class="text-error">
            требуется создание
          </span>
          <!-- <v-chip class="text-error">
            требуется отзыв
          </v-chip> -->
        </div>
      </template>
    </div>
  </div>
  <ClientReviewDialog
    ref="clientReviewDialog"
    @updated="onUpdated"
    @created="onCreated"
    @deleted="onDeleted"
  />
</template>
