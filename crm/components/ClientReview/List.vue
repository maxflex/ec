<script setup lang="ts">
import type { ClientReviewDialog } from '#build/components'

const props = defineProps<{
  items: ClientReviewListResource[]
}>()
const { items } = toRefs(props)
const clientReviewDialog = ref<InstanceType<typeof ClientReviewDialog>>()

function isRealClientReview(r: ClientReviewListResource): r is RealClientReviewItem {
  return 'rating' in r
}

function onUpdated(r: RealClientReviewItem) {
  const index = items.value.findIndex(e => e.id === r.id)
  if (index === -1) {
    return
  }
  items.value[index] = r
  itemUpdated('clientReview', r.id)
}

function onCreated(r: RealClientReviewItem, fakeItemId: string) {
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
      <div style="width: 170px">
        <NuxtLink
          :to="{ name: 'teachers-id', params: { id: r.teacher.id } }"
        >
          {{ formatNameInitials(r.teacher) }}
        </NuxtLink>
      </div>
      <div style="width: 210px">
        <NuxtLink
          :to="{ name: 'clients-id', params: { id: r.client.id } }"
        >
          {{ formatName(r.client) }}
        </NuxtLink>
      </div>
      <div style="width: 140px">
        {{ ProgramShortLabel[r.program] }}
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
        <div class="text-gray" style="width: 200px">
          отзыв от {{ formatDate(r.created_at) }}
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
