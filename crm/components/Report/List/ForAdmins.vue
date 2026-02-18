<script setup lang="ts">
import type { RealReport, ReportListResource } from '..'
import { mdiAutoFix, mdiCheck, mdiCheckAll } from '@mdi/js'

const props = defineProps<{
  items: ReportListResource[]
}>()
const { items } = toRefs(props)

function isRealReport(r: ReportListResource): r is RealReport {
  return 'status' in r
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
      <div style="width: 100px">
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
          <div v-if="r.status === 'published'" class="text-gray text-caption">
            {{ r.is_read ? 'прочитано' : 'не прочитано' }}
          </div>
          <!-- <v-icon
            v-if="r.status === 'published'"
            class="ml-2"
            :icon="r.is_read ? mdiCheckAll : mdiCheck"
            size="20"
            color="secondary"
          /> -->
        </div>
        <div style="width: 70px">
          <span v-if="r.price">
            {{ formatPrice(r.price) }} ₽
          </span>
        </div>
        <div style="width: 30px">
          <v-icon
            v-if="r.has_ai_comment"
            :icon="mdiAutoFix"
          />
        </div>
        <div style="width: 30px">
          <span v-if="r.grade" :class="`text-score text-score--${r.grade}`">
            {{ r.grade }}
          </span>
        </div>

        <div style="width: 100px" class="pr-2">
          <ReportFill v-model="r.fill" />
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
        <div style="width: 160px; flex: initial" class="">
          <span v-if="r.is_required" class="text-error">
            требуется отчёт
          </span>
        </div>
      </template>
    </div>
  </div>
</template>
