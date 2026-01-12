<script setup lang="ts">
import type { RealReport, ReportListResource } from '..'
import { mdiCheck, mdiCheckAll } from '@mdi/js'

const props = defineProps<{
  items: ReportListResource[]
}>()
const { items } = toRefs(props)

function isRealReport(r: ReportListResource): r is RealReport {
  return 'status' in r
}
</script>

<template>
  <Table>
    <TableRow v-for="r in items" :id="`report-${r.id}`" :key="r.id">
      <TableCol :width="150">
        <UiPerson :item="r.teacher" />
      </TableCol>
      <TableCol :width="180">
        <UiPerson :item="r.client" />
      </TableCol>
      <TableCol :width="120">
        {{ ProgramShortLabel[r.program] }}
      </TableCol>

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
        <TableCol :width="120">
          занятий: {{ r.lessons_count }}
          <div v-if="r.count" class="text-gray text-caption">
            +{{ plural(r.count, ['отчёт', 'отчёта', 'отчётов']) }}
          </div>
        </TableCol>
        <TableCol>
          <ReportStatus :status="r.status" />
          <v-icon
            v-if="r.status === 'published'"
            class="ml-2"
            :icon="r.is_read ? mdiCheckAll : mdiCheck"
            size="20"
            color="secondary"
          />
        </TableCol>
        <TableCol :width="70">
          <span v-if="r.price">
            {{ formatPrice(r.price) }} ₽
          </span>
        </TableCol>
        <TableCol :width="30">
          <span v-if="r.grade" :class="`text-score text-score--${r.grade}`">
            {{ r.grade }}
          </span>
        </TableCol>
        <TableCol :width="100" class="pr-2">
          <ReportFill v-model="r.fill" />
        </TableCol>

        <TableCol style="width: 100px; flex: initial" class="text-gray">
          <span v-if="r.to_check_at">
            {{ formatTextDate(r.to_check_at, true) }}
          </span>
        </TableCol>
      </template>

      <!--      fake report -->
      <template v-else>
        <TableCol style="width: 100px">
          занятий: {{ r.lessons_count }}
          <div v-if="r.count" class="text-gray text-caption">
            +{{ plural(r.count, ['отчёт', 'отчёта', 'отчётов']) }}
          </div>
        </TableCol>
        <TableCol style="width: 160px; flex: initial" class="">
          <span v-if="r.is_required" class="text-error">
            требуется отчёт
          </span>
        </TableCol>
      </template>
    </TableRow>
  </Table>
</template>
