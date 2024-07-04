<script setup lang="ts">
import { mdiAccountGroup } from '@mdi/js'

const { item, editable, conductable } = defineProps<{
  item: LessonListResource
  editable?: boolean
  conductable?: boolean
}>()

const emit = defineEmits<{
  edit: [id: number]
  conduct: [id: number, status: LessonStatus]
}>()
</script>

<template>
  <div
    :id="`lesson-${item.id}`"
    :class="`lesson-list__status--${item.status}`"
  >
    <div v-if="editable || conductable" class="table-actionss">
      <v-btn
        icon="$edit"
        :size="48"
        variant="plain"
        color="gray"
        @click="editable ? emit('edit', item.id) : emit('conduct', item.id, item.status)"
      />
    </div>
    <div style="width: 110px" />
    <div style="width: 120px">
      {{ item.time }} – {{ item.time_end }}
    </div>
    <div style="width: 80px">
      К–{{ item.cabinet }}
    </div>
    <div v-if="item.teacher" style="width: 150px">
      <NuxtLink
        :to="{ name: 'teachers-id', params: { id: item.teacher.id } }"
      >
        {{ formatNameShort(item.teacher) }}
      </NuxtLink>
    </div>
    <div style="width: 90px">
      <NuxtLink :to="{ name: 'groups-id', params: { id: item.group.id } }">
        ГР-{{ item.group.id }}
      </NuxtLink>
    </div>
    <div style="width: 120px">
      {{ ProgramShortLabel[item.group.program] }}
    </div>
    <div style="width: 80px; display: flex; align-items: center">
      <v-icon :icon="mdiAccountGroup" class="mr-2 vfn-1" />
      {{ item.group.contracts_count }}
    </div>
    <div style="width: 140px">
      <LessonStatus2 :status="item.status" />
    </div>
    <div>
      <v-chip v-if="item.is_first" class="text-deepOrange">
        первое
      </v-chip>
      <v-chip v-else-if="item.is_unplanned" class="text-purple">
        внеплановое
      </v-chip>
    </div>
  </div>
</template>
