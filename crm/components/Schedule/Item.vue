<script setup lang="ts">
import { mdiAccountGroup, mdiDoor } from '@mdi/js'

const { item } = defineProps<{
  item: ScheduleItem
}>()
</script>

<template>
  <div :id="`lesson-${item.id}`">
    <div class="font-weight-medium">
      <LessonStatus :status="item.status" />
      <span> {{ item.time }} – {{ item.time_end }} </span>
      <v-spacer />
      <span v-if="item.is_first" class="text-success vfn-1">
        первое
      </span>
      <span v-else-if="item.is_unplanned" class="text-purple vfn-1">
        внепл.
      </span>
    </div>
    <div v-if="item.teacher">
      <NuxtLink :to="{ name: 'teachers-id', params: { id: item.teacher.id } }">
        {{ formatNameShort(item.teacher) }}
      </NuxtLink>
    </div>
    <div style="gap: 8px">
      <NuxtLink :to="{ name: 'groups-id', params: { id: item.group.id } }">
        ГР-{{ item.group.id }}
      </NuxtLink>
      <div>
        {{ ProgramShortLabel[item.group.program] }}
      </div>
    </div>
    <div style="display: flex; gap: 12px">
      <span v-if="item.group.contracts_count" style="display: flex; align-items: center; gap: 4px">
        <v-icon :icon="mdiAccountGroup" />
        {{ item.group.contracts_count }}
      </span>
      <span style="display: flex; align-items: center; gap: 2px; left: -4px; position: relative;">
        <v-icon :icon="mdiDoor" />
        К-{{ CabinetLabel[item.cabinet!] }}
      </span>
    </div>
    <!-- <div v-if="item.contractLesson" class="schedule-calendar__lesson-contract">
      {{ ContractLessonStatusLabel[item.contractLesson.status] }}
      <span v-if="item.contractLesson.minutes_late">
        на {{ item.contractLesson.minutes_late }} мин
      </span>
    </div> -->
    <!-- <div v-if="editable" class="table-actionss">
    <v-btn
      variant="plain"
      icon="$edit"
      :size="36"
      @click="lessonDialog?.edit(item.id)"
    />
  </div> -->
  </div>
</template>
