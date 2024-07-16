<script setup lang="ts">
const { items } = defineProps<{ items: ExamScoreResource[] }>()
const emit = defineEmits<{
  edit: [e: ExamScoreResource]
}>()
</script>

<template>
  <div class="table">
    <div v-for="e in items" :id="`exam-score-${e.id}`" :key="e.id">
      <div class="table-actionss">
        <v-btn
          icon="$edit"
          :size="48"
          variant="plain"
          @click="emit('edit', e)"
        />
      </div>
      <div style="width: 200px">
        <NuxtLink :to="{ name: 'clients-id', params: { id: e.client!.id } }">
          {{ formatName(e.client!) }}
        </NuxtLink>
      </div>
      <div style="width: 150px">
        {{ ExamLabel[e.exam!] }}
      </div>
      <div style="width: 200px">
        {{ YearLabel[e.year] }}
      </div>
      <div>
        балл: {{ e.score }}
      </div>
    </div>
  </div>
</template>
