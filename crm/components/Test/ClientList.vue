<script setup lang="ts">
import { PROGRAM } from "~/utils/sment"
import { plural } from "~/utils/filters"
const { tests } = defineProps<{ tests: ClientTest[] }>()
</script>

<template>
  <div class="table table--hover">
    <div v-for="t in tests" :key="t.id">
      <div style="width: 220px">
        {{ t.name }}
      </div>
      <div style="width: 250px">
        <template v-if="t.program">
          {{ PROGRAM[t.program] }}
        </template>
        <span class="text-gray" v-else> не установлено </span>
      </div>
      <div style="width: 150px">{{ t.minutes }} минут</div>
      <div>
        <template v-if="t.questions_count">
          {{ plural(t.questions_count, ["вопрос", "вопроса", "вопросов"]) }}
        </template>
        <span class="text-gray" v-else> нет вопросов </span>
      </div>
      <div class="table-actions">
        <router-link
          v-if="t.is_finished"
          :to="{ name: 'client-tests-result-id', params: { id: t.id } }"
        >
          <TestResult :test="t" />
          баллов
        </router-link>
        <v-btn
          v-else
          color="primary"
          :to="{
            name: 'client-tests-start-id',
            params: {
              id: t.id,
            },
          }"
        >
          начать тест
        </v-btn>
      </div>
    </div>
  </div>
</template>
