<script setup lang="ts">
import type { GradeDialog } from '#build/components'

const props = defineProps<{
  items: GradeListResource[]
}>()
const { items } = toRefs(props)
const gradeDialog = ref<InstanceType<typeof GradeDialog>>()

function isRealGrade(g: GradeListResource): g is RealGradeItem {
  return 'grade' in g
}

function onUpdated(g: RealGradeItem) {
  const index = items.value.findIndex(e => e.id === g.id)
  if (index === -1) {
    return
  }
  items.value[index] = g
  itemUpdated('grade', g.id)
}

function onCreated(g: RealGradeItem, fakeItemId: string) {
  const index = items.value.findIndex(e => e.id === fakeItemId)
  if (index === -1) {
    return
  }
  items.value[index] = g
  itemUpdated('grade', g.id)
}

function onDeleted(g: GradeResource) {
  const index = items.value.findIndex(e => e.id === g.id)
  if (index !== -1) {
    items.value.splice(index, 1)
  }
}
</script>

<template>
  <div class="table">
    <div v-for="g in items" :id="`grade-${g.id}`" :key="g.id">
      <div style="width: 210px">
        <NuxtLink
          :to="{ name: 'clients-id', params: { id: g.client.id } }"
        >
          {{ formatName(g.client) }}
        </NuxtLink>
      </div>
      <div style="width: 150px">
        {{ ProgramShortLabel[g.program] }}
      </div>
      <div style="width: 180px">
        {{ YearLabel[g.year] }}
      </div>
      <div style="width: 150px">
        {{ QuarterLabel[g.quarter] }}
      </div>
      <div style="flex: 1" />
      <template v-if="isRealGrade(g)">
        <div class="table-actionss">
          <v-btn
            icon="$edit"
            :size="48"
            variant="plain"
            @click="gradeDialog?.edit(g.id)"
          />
        </div>
        <div style="width: 140px; flex: initial">
          <span :class="`score score--${g.grade}`" class="mr-2">
            {{ g.grade }}
          </span>
          {{ LessonScoreLabel[g.grade] }}
        </div>
      </template>
      <template v-else>
        <div class="table-actionss">
          <v-btn
            icon="$edit"
            :size="48"
            variant="plain"
            @click="gradeDialog?.create(g)"
          />
        </div>
        <div style="width: 140px; flex: initial">
          <span class="text-error">
            требуется оценка
          </span>
        </div>
      </template>
    </div>
  </div>
  <GradeDialog
    ref="gradeDialog"
    @updated="onUpdated"
    @created="onCreated"
    @deleted="onDeleted"
  />
</template>
