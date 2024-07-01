<script setup lang="ts">
import { mdiCheckAll, mdiWeb } from '@mdi/js'
import type { ReportDialog } from '#build/components'

const props = defineProps<{
  items: ReportListResource[]
}>()
const { user } = useAuthStore()
const { items } = toRefs(props)
const reportDialog = ref<InstanceType<typeof ReportDialog>>()

function isRealReport(r: ReportListResource): r is RealReportItem {
  return 'created_at' in r
}

function onUpdated(r: RealReportItem) {
  const index = items.value.findIndex(e => e.id === r.id)
  if (index === -1) {
    return
  }
  items.value[index] = r
  itemUpdated('report', r.id)
}

function onCreated(r: RealReportItem, fakeItemId: string) {
  const index = items.value.findIndex(e => e.id === fakeItemId)
  if (index === -1) {
    return
  }
  items.value[index] = r
  itemUpdated('report', r.id)
}

function onDeleted(r: RealReportItem) {
  const index = items.value.findIndex(e => e.id === r.id)
  if (index !== -1) {
    items.value.splice(index, 1)
  }
}
</script>

<template>
  <div class="table">
    <div v-for="r in items" :id="`report-${r.id}`" :key="r.id">
      <div style="width: 170px">
        <NuxtLink
          :to="{ name: 'teachers-id', params: { id: r.teacher.id } }"
        >
          {{ formatNameShort(r.teacher) }}
        </NuxtLink>
      </div>
      <div style="width: 210px">
        <NuxtLink
          :to="{ name: 'clients-id', params: { id: r.client.id } }"
        >
          {{ formatName(r.client) }}
        </NuxtLink>
      </div>
      <div style="width: 180px">
        {{ YearLabel[r.year] }}
      </div>
      <div style="width: 130px">
        {{ ProgramShortLabel[r.program] }}
      </div>
      <template v-if="isRealReport(r)">
        <div class="table-actionss">
          <v-btn
            icon="$edit"
            :size="48"
            variant="plain"
            @click="reportDialog?.edit(r.id)"
          />
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
        <div v-if="user?.entity_type === EntityType.teacher" class="table-actionss">
          <v-btn
            icon="$edit"
            :size="48"
            variant="plain"
            @click="reportDialog?.create(r)"
          />
        </div>
        <div style="width: 100px; flex: 1">
          прошло занятий: {{ r.lessons_since_last_report }}
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
