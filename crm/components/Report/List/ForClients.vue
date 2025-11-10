<script setup lang="ts">
import type { RealReport } from '..'

const { items } = defineProps<{
  items: RealReport[]
}>()

const router = useRouter()
</script>

<template>
  <div class="table table--hover">
    <div
      v-for="r in items"
      :id="`report-${r.id}`"
      :key="r.id"
      class="cursor-pointer"
      @click="router.push({ name: 'reports-id', params: { id: r.id } })"
    >
      <div style="width: 170px">
        <UiPerson :item="r.teacher" />
      </div>
      <div style="width: 140px">
        {{ ProgramShortLabel[r.program] }}
      </div>
      <div style="width: 120px">
        занятий: {{ r.lessons_count }}
      </div>

      <div style="width: 50px">
        <span v-if="r.grade" :class="`text-score text-score--${r.grade}`">
          {{ r.grade }}
        </span>
      </div>
      <div style="width: 100px; flex: initial" class="text-gray">
        <span v-if="r.to_check_at">
          {{ formatTextDate(r.to_check_at, true) }}
        </span>
      </div>
    </div>
  </div>
</template>
