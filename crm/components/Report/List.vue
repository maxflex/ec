<script setup lang="ts">
import { mdiCheckAll, mdiWeb } from '@mdi/js'
import type { ReportDialog } from '#build/components'

const props = defineProps<{
  items: ReportListResource[]
}>()
const { isAdmin, isTeacher, isClient } = useAuthStore()
const { items } = toRefs(props)
const reportDialog = ref<InstanceType<typeof ReportDialog>>()

function isRealReport(r: ReportListResource): r is RealReport {
  return 'created_at' in r
}

function isEditable(r: RealReport): boolean {
  if (isTeacher) {
    return !r.is_moderated
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
      <div v-if="!isTeacher" style="width: 170px">
        <UiPerson :item="r.teacher" />
      </div>
      <div v-if="!isClient" style="width: 220px">
        <UiPerson :item="r.client" />
      </div>
      <div style="width: 140px">
        {{ ProgramShortLabel[r.program] }}
      </div>
      <template v-if="isRealReport(r)">
        <div class="table-actionss">
          <v-btn
            icon="$eye"
            :size="48"
            variant="plain"
            color="gray"
            :to="{ name: 'reports-id', params: { id: r.id } }"
          />
          <v-btn
            v-if="isEditable(r)"
            icon="$edit"
            :size="48"
            variant="plain"
            @click="reportDialog?.edit(r.id)"
          />
        </div>
        <div style="width: 200px">
          прошло занятий: {{ r.lessons_count }}
        </div>
        <div v-if="!isClient" style="width: 110px">
          <span v-if="r.price">
            {{ formatPrice(r.price) }} руб.
          </span>
        </div>
        <div
          style="width: 100px; flex: 1"
          class="text-center d-flex ga-5"
        >
          <v-icon
            :class="{
              'opacity-2': !r.is_published,
            }"
            :icon="mdiWeb"
            :color="r.is_published ? 'secondary' : 'gray'"
          />
          <v-icon
            :class="{
              'opacity-2': !r.is_moderated,
            }"
            :icon="mdiCheckAll"
            :color="r.is_moderated ? 'secondary' : 'gray'"
          />
        </div>
        <div style="width: 160px; flex: initial" class="text-gray">
          отчёт от {{ formatTextDate(r.created_at, true) }}
        </div>
      </template>
      <template v-else>
        <div v-if="isTeacher" class="table-actionss">
          <v-btn
            icon="$edit"
            :size="48"
            variant="plain"
            @click="reportDialog?.create(r)"
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
