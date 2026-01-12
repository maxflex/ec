<script setup lang="ts">
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
  <Table>
    <TableRow v-for="r in items" :id="`report-${r.id}`" :key="r.id">
      <TableCol :width="190">
        <UiPerson :item="r.client" />
      </TableCol>
      <TableCol :width="100">
        {{ ProgramShortLabel[r.program] }}
      </TableCol>
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
        <TableCol :width="110">
          занятий: {{ r.lessons_count }}
          <div v-if="r.count" class="text-gray text-caption">
            +{{ plural(r.count, ['отчёт', 'отчёта', 'отчётов']) }}
          </div>
        </TableCol>
        <TableCol :width="100">
          <span v-if="r.price">
            {{ formatPrice(r.price) }} руб.
          </span>
        </TableCol>
        <TableCol
          :width="150"
          class="text-center d-flex ga-5"
        >
          <ReportStatus :status="r.status" />
        </TableCol>

        <TableCol :width="120">
          {{ r.is_read ? 'прочитано' : 'не прочитано' }}
        </TableCol>

        <TableCol :width="50">
          <span v-if="r.grade" :class="`text-score text-score--${r.grade}`">
            {{ r.grade }}
          </span>
        </TableCol>

        <TableCol :width="100" class="pr-2">
          <v-progress-linear
            bg-color="#92aed9"
            :color="getFillColor(r)"
            height="12"
            max="100"
            min="0"
            :model-value="r.fill"
            rounded
          />
        </TableCol>

        <TableCol style="width: 100px; flex: initial" class="text-gray">
          <span v-if="r.to_check_at">
            {{ formatTextDate(r.to_check_at, true) }}
          </span>
        </TableCol>
      </template>
      <template v-else>
        <div class="table-actionss">
          <v-btn
            icon="$edit"
            :size="48"
            variant="plain"
            color="gray"
            :to="{
              name: 'reports-create',
              query: {
                teacher_id: r.teacher.id,
                client_id: r.client.id,
                year: r.year,
                program: r.program,
              },
            }"
          />
        </div>
        <TableCol style="width: 100px">
          занятий: {{ r.lessons_count }}
          <div v-if="r.count" class="text-gray text-caption">
            +{{ plural(r.count, ['отчёт', 'отчёта', 'отчётов']) }}
          </div>
        </TableCol>
        <TableCol style="width: 160px; flex: initial">
          <span v-if="r.is_required" class="text-error">
            требуется отчёт
          </span>
        </TableCol>
      </template>
    </TableRow>
  </Table>
</template>
