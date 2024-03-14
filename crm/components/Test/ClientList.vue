<script setup lang="ts">
import type { Test, Tests } from "~/utils/models"
import { PROGRAM } from "~/utils/sment"
import { plural } from "~/utils/filters"

const { tests } = defineProps<{ tests: Tests }>()
const emit = defineEmits<{ (e: "open", test: Test): void }>()
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
        <template v-if="t.answers?.length">
          {{ plural(t.answers.length, ["вопрос", "вопроса", "вопросов"]) }}
        </template>
        <span class="text-gray" v-else> нет вопросов </span>
      </div>
      <div class="table-actions">
        <v-btn
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
