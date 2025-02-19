<script setup lang="ts">
import type { ClientReviewDialog } from '#build/components'
import type { ClientReviewListResource } from '.'

const props = defineProps<{
  items: ClientReviewListResource[]
  clientId?: number
  teacherId?: number
}>()
const { items } = toRefs(props)
const { clientId, teacherId } = props
const clientReviewDialog = ref<InstanceType<typeof ClientReviewDialog>>()
const { isTeacher } = useAuthStore()

function onUpdated(cr: ClientReviewListResource) {
  const index = items.value.findIndex(e => e.id === cr.id)
  if (index === -1) {
    return
  }
  items.value[index] = cr
  itemUpdated('client-review', cr.id as number)
}

function onCreated(cr: ClientReviewListResource, fakeItemId: string) {
  const index = items.value.findIndex(e => e.id === fakeItemId)
  if (index === -1) {
    return
  }
  items.value[index] = cr
  itemUpdated('client-review', cr.id as number)
}

function onDeleted(id: number) {
  const index = items.value.findIndex(e => e.id === id)
  if (index !== -1) {
    items.value.splice(index, 1)
  }
}
</script>

<template>
  <div class="table table--padding">
    <div v-for="cr in items" :id="`client-review-${cr.id}`" :key="cr.id">
      <div v-if="!isTeacher && !teacherId" style="width: 180px">
        <UiPerson :item="cr.teacher" />
      </div>
      <div v-if="!clientId" style="width: 180px">
        <UiPerson :item="cr.client" />
      </div>
      <div style="width: 110px">
        {{ ProgramShortLabel[cr.program] }}
      </div>
      <div style="width: 120px">
        занятий: {{ cr.lessons_count }}
      </div>
      <div style="width: 180px">
        <div v-for="year in cr.years" :key="year">
          {{ YearLabel[year] }}
        </div>
      </div>
      <div style="flex: 1">
        <div v-for="es in cr.exam_scores" :key="es.id">
          {{ ExamLabel[es.exam] }}: {{ es.score }}
        </div>
      </div>
      <template v-if="typeof (cr.id) === 'number'">
        <div class="table-actionss">
          <v-btn
            icon="$edit"
            :size="48"
            variant="plain"
            @click="clientReviewDialog?.edit(cr.id)"
          />
        </div>
        <div style="width: 30px; flex: initial">
          <span :class="`text-score text-score--${cr.rating}`">
            {{ cr.rating }}
          </span>
        </div>
      </template>
      <template v-else>
        <div class="table-actionss">
          <v-btn
            icon="$edit"
            :size="48"
            variant="plain"
            @click="clientReviewDialog?.create(cr)"
          />
        </div>
        <div class="text-right">
          <span class="text-error">
            требуется создание
          </span>
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
