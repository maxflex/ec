<script setup lang="ts">
import type { ClientReviewDialog } from '#build/components'

const props = defineProps<{
  items: ClientReviewListResource[]
  clientId?: number
  teacherId?: number
}>()
const { items } = toRefs(props)
const { clientId, teacherId } = props
const clientReviewDialog = ref<InstanceType<typeof ClientReviewDialog>>()
const { isTeacher } = useAuthStore()

function onUpdated(r: ClientReviewResource) {
  const index = items.value.findIndex(e => e.id === r.id)
  if (index === -1) {
    return
  }
  // items.value[index] = r
  itemUpdated('clientReview', r.id)
}

function onCreated(r: ClientReviewResource, fakeItemId: string) {
  const index = items.value.findIndex(e => e.id === fakeItemId)
  if (index === -1) {
    return
  }
  // items.value[index] = r
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
      <div v-if="!isTeacher && !teacherId" style="width: 150px">
        <UiPerson :item="r.teacher" />
      </div>
      <div v-if="!clientId" style="width: 180px">
        <UiPerson :item="r.client" />
      </div>
      <div style="width: 110px">
        {{ ProgramShortLabel[r.program] }}
      </div>
      <div style="width: 110px">
        занятий: {{ r.lessons_count }}
      </div>
      <div style="width: 150px">
        <div v-for="year in r.years" :key="year">
          {{ YearLabel[year] }}
        </div>
      </div>
      <template v-if="typeof (r.id) === 'number'">
        <div class="table-actionss">
          <v-btn
            icon="$edit"
            :size="48"
            variant="plain"
            @click="clientReviewDialog?.edit(r.id)"
          />
        </div>
        <div style="flex: 1" class="text-truncate">
          {{ r.text }}
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
