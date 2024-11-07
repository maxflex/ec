<script setup lang="ts">
import type { ReportDialog } from '#components'

const props = defineProps<{
  items: ReportListResource[]
}>()
const router = useRouter()
const { isAdmin, isTeacher } = useAuthStore()
const { items } = toRefs(props)
const reportDialog = ref<InstanceType<typeof ReportDialog>>()

function isRealReport(r: ReportListResource): r is RealReport {
  return 'created_at' in r
}

function getFillColor(r: RealReport) {
  if (r.fill > 80) {
    return 'success'
  }
  if (r.fill > 50) {
    return 'orange'
  }
  return 'error'
}

function isEditable(r: RealReport): boolean {
  if (isTeacher) {
    return r.status === 'published'
  }
  else if (isAdmin) {
    return true
  }
  return false
}

function onUpdated(r: RealReport) {
  const index = items.value.findIndex(e => e.id === r.id)
  if (index === -1) {
    return
  }
  items.value[index] = r
  itemUpdated('report', r.id)
}

function onCreated(r: RealReport, fakeItemId: string) {
  const index = items.value.findIndex(e => e.id === fakeItemId)
  if (index === -1) {
    return
  }
  items.value[index] = r
  itemUpdated('report', r.id)
}

function onDeleted(r: ReportResource) {
  const index = items.value.findIndex(e => e.id === r.id)
  if (index !== -1) {
    items.value.splice(index, 1)
  }
}
</script>

<template>
  <div class="table">
    <div v-for="r in items" :id="`report-${r.id}`" :key="r.id">
      <div v-if="!isTeacher" style="width: 150px">
        <UiPerson :item="r.teacher" />
      </div>
      <div style="width: 180px">
        <UiPerson :item="r.client" />
      </div>
      <div style="width: 120px">
        {{ ProgramShortLabel[r.program] }}
      </div>
      <template v-if="isRealReport(r)">
        <div class="table-actionss">
          <v-btn
            v-if="isEditable(r)"
            icon="$edit"
            :size="48"
            variant="plain"
            color="gray"
            :to="{ name: 'reports-id-edit', params: { id: r.id } }"
          />
        </div>
        <div style="width: 150px">
          прошло занятий: {{ r.lessons_count }}
        </div>
        <div style="width: 70px">
          <span v-if="r.price">
            {{ formatPrice(r.price) }} руб.
          </span>
        </div>
        <div style="width: 30px">
          <span v-if="r.grade" :class="`score score--${r.grade}`">
            {{ r.grade }}
          </span>
        </div>

        <div
          style="width: 150px"
          class="text-center d-flex ga-5"
        >
          {{ ReportStatusLabel[r.status] }}
        </div>

        <div style="width: 100px" class="pr-2">
          <v-progress-linear
            bg-color="#92aed9"
            :color="getFillColor(r)"
            height="12"
            max="100"
            min="0"
            :model-value="r.fill"
            rounded
          />
        </div>

        <div style="width: 100px; flex: initial" class="text-gray">
          {{ formatTextDate(r.created_at, true) }}
        </div>
      </template>
      <template v-else>
        <div v-if="isTeacher" class="table-actionss">
          <v-btn
            icon="$edit"
            :size="48"
            variant="plain"
            color="gray"
            :to="{ name: 'reports-id-edit', params: { id: r.id } }"
          />
        </div>
        <div style="width: 100px; flex: 1">
          прошло занятий: {{ r.lessons_count }}
        </div>
        <div style="width: 160px; flex: initial">
          <span class="text-error">
            требуется отчёт
          </span>
          <!-- <v-chip class="text-error">
            требуется отчёт
          </v-chip> -->
        </div>
      </template>
    </div>
  </div>
  <ReportDialog
    ref="reportDialog"
    @updated="onUpdated"
    @created="onCreated"
    @deleted="onDeleted"
  />
</template>
