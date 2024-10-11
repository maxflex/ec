<script setup lang="ts">
import type { ClientReviewDialog } from '#build/components'

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
      <div v-if="!isTeacher" style="width: 170px">
        <UiPerson :item="r.teacher" />
      </div>
      <div style="width: 210px">
        <UiPerson :item="r.client" />
      </div>
      <div style="width: 100px">
        {{ ProgramShortLabel[r.program] }}
      </div>
      <div style="width: 180px">
        прошло занятий: {{ r.lessons_count }}
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
        <div style="width: 220px">
          {{ filterTruncate(r.text, 20) }}
        </div>
        <div class="text-gray" style="width: 100px">
          {{ formatDate(r.created_at) }}
        </div>
        <div style="justify-content: flex-end;" class="d-flex">
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
