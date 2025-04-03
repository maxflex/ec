<script setup lang="ts">
import { mdiChevronRight } from '@mdi/js'

const { items } = defineProps<{
  items: RealReport[]
}>()
</script>

<template>
  <div class="table table--padding">
    <RouterLink
      v-for="r in items"
      :id="`report-${r.id}`"
      :key="r.id"
      :to="{ name: 'reports-id', params: { id: r.id } }"
      style="align-items: flex-start;"
      class="pr-2"
    >
      <div style="flex: 1">
        <div>
          <UiPerson :item="r.teacher" />
        </div>
        <div>
          {{ ProgramShortLabel[r.program] }}
        </div>
      </div>
      <div style="width: 90px">
        <div>
          занятий: {{ r.lessons_count }}
        </div>
        <div>
          оценка:
          <span v-if="r.grade" :class="`text-score text-score--${r.grade}`" style="font-size: 14px">
            {{ r.grade }}
          </span>
        </div>

        <div class="text-gray">
          {{ formatDate(r.to_check_at) }}
        </div>
      </div>
      <div style="width: 20px; flex: initial; align-self: center;" class="text-right">
        <v-icon :icon="mdiChevronRight" color="gray" />
      </div>
    </RouterLink>
  </div>
</template>
