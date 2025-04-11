<script setup lang="ts">
import type { TestResource } from '.'

const { tests } = defineProps<{ tests: TestResource[] }>()
const emit = defineEmits<{
  open: [t: TestResource]
}>()
</script>

<template>
  <div class="table table--padding">
    <div v-for="t in tests" :key="t.id">
      <div style="width: 380px">
        {{ t.name }}
        <div v-if="t.description">
          {{ t.description }}
        </div>
      </div>
      <div style="width: 200px">
        {{ t.minutes }} минут
        <div>
          <span v-if="t.questions?.length">
            {{ plural(t.questions.length, ["вопрос", "вопроса", "вопросов"]) }}
          </span>
          <span v-else class="text-gray"> нет вопросов </span>
        </div>
      </div>
      <div style="width: 100px" class="font-weight-bold">
        {{ t.max_score }} баллов
      </div>
      <div class="table-actionss">
        <v-btn
          variant="plain"
          color="gray"
          icon="$edit"
          :size="48"
          @click="emit('open', t)"
        />
      </div>
    </div>
  </div>
</template>
