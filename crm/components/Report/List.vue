<script setup lang="ts">
import { mdiCheckAll, mdiWeb } from '@mdi/js'

const { items, editable } = defineProps<{
  items: ReportListResource[]
  editable?: boolean
}>()
const emit = defineEmits<{
  edit: [r: ReportListResource]
}>()
</script>

<template>
  <div class="table">
    <div v-for="r in items" :id="`report-${r.id}`" :key="r.id">
      <div v-if="editable" class="table-actionss">
        <v-btn
          icon="$edit"
          :size="48"
          variant="plain"
          @click="emit('edit', r)"
        />
      </div>
      <div style="width: 60px">
        {{ r.id }}
      </div>
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
      <div style="width: 110px">
        {{ ProgramShortLabel[r.program] }}
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
      <div style="width: 150px; flex: initial" class="text-gray">
        {{ formatDateTime(r.created_at) }}
      </div>
    </div>
  </div>
</template>
