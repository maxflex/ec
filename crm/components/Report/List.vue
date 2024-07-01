<script setup lang="ts">
import { mdiCheckAll, mdiWeb } from '@mdi/js'

const { items } = defineProps<{
  items: ReportListResource[]
}>()

const emit = defineEmits<{
  edit: [r: RealReportItem]
  create: [r: FakeReportItem]
}>()

function isRealReport(r: ReportListResource): r is RealReportItem {
  return 'created_at' in r
}
</script>

<template>
  <div class="table">
    <div v-for="r in items" :id="`report-${r.id}`" :key="r.id">
      <!-- <div style="width: 60px">
        {{ r.id }}
      </div> -->
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
            @click="emit('edit', r)"
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
        <div class="table-actionss">
          <v-btn
            icon="$edit"
            :size="48"
            variant="plain"
            @click="emit('create', r)"
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
</template>
