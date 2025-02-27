<script setup lang="ts">
import { mdiCheckAll } from '@mdi/js'

const props = defineProps<{
  items: ReportListResource[]
}>()
const { items } = toRefs(props)

function isRealReport(r: ReportListResource): r is RealReport {
  return 'status' in r
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
</script>

<template>
  <div class="table">
    <div v-for="r in items" :id="`report-${r.id}`" :key="r.id">
      <div style="width: 150px">
        <UiPerson :item="r.teacher" />
      </div>
      <div style="width: 180px">
        <UiPerson :item="r.client" />
      </div>
      <div style="width: 120px">
        {{ ProgramShortLabel[r.program] }}
      </div>

      <!--    real report -->
      <template v-if="isRealReport(r)">
        <div class="table-actionss">
          <v-btn
            icon="$edit"
            :size="48"
            variant="plain"
            color="gray"
            :to="{ name: 'reports-id-edit', params: { id: r.id } }"
          />
        </div>
        <div style="width: 120px">
          занятий: {{ r.lessons_count }}
          <div v-if="r.count" class="text-gray text-caption">
            +{{ plural(r.count, ['отчёт', 'отчёта', 'отчётов']) }}
          </div>
        </div>
        <div style="flex: 1">
          <ReportStatus :status="r.status" />
          <v-icon
            v-if="r.delivery !== null"
            class="ml-2"
            :icon="mdiCheckAll"
            size="20"
            :color="r.delivery === 'read' ? 'secondary' : 'gray'"
          />
        </div>
        <div style="width: 70px">
          <span v-if="r.price">
            {{ formatPrice(r.price) }} ₽
          </span>
        </div>
        <div style="width: 30px">
          <span v-if="r.grade" :class="`text-score text-score--${r.grade}`">
            {{ r.grade }}
          </span>
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
          <span v-if="r.to_check_at">
            {{ formatTextDate(r.to_check_at, true) }}
          </span>
        </div>
      </template>

      <!--      fake report -->
      <template v-else>
        <div style="width: 100px; flex: 1">
          занятий: {{ r.lessons_count }}
          <div v-if="r.count" class="text-gray text-caption">
            +{{ plural(r.count, ['отчёт', 'отчёта', 'отчётов']) }}
          </div>
        </div>
        <div style="width: 160px; flex: initial">
          <ReportRequirement :requirement="r.requirement" />
        </div>
      </template>
    </div>
  </div>
</template>
