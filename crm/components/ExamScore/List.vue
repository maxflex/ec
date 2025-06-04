<script setup lang="ts">
import { mdiWeb } from '@mdi/js'

const { items } = defineProps<{ items: ExamScoreResource[] }>()
const emit = defineEmits<{
  edit: [e: ExamScoreResource]
}>()
</script>

<template>
  <div class="table">
    <div v-for="item in items" :id="`exam-score-${item.id}`" :key="item.id">
      <UiTableActions>
        <v-btn
          icon="$edit"
          :size="48"
          variant="plain"
          @click="emit('edit', item)"
        />
</UiTableActions>
      <div style="width: 200px">
        <NuxtLink :to="{ name: 'clients-id', params: { id: item.client!.id } }">
          {{ formatName(item.client!) }}
        </NuxtLink>
      </div>
      <div style="width: 220px">
        {{ ExamLabel[item.exam!] }}
      </div>
      <div style="width: 200px">
        {{ YearLabel[item.year] }}
      </div>
      <div style="width: 150px">
        балл: {{ item.score }}
      </div>
      <div>
        <v-icon :icon="mdiWeb" :class="item.is_published ? 'text-secondary' : 'opacity-2 text-gray'" />
      </div>
    </div>
  </div>
</template>
