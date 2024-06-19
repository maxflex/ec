<script setup lang="ts">
import type { ClientTest } from '~/utils/models'
import { ENTITY_TYPE, PROGRAM } from '~/utils/sment'
import { plural } from '~/utils/filters'

const { tests } = defineProps<{ tests: ClientTest[] }>()
const { user } = useAuthStore()
</script>

<template>
  <div class="table table--hover">
    <div
      v-for="t in tests"
      :key="t.id"
    >
      <div style="width: 220px">
        {{ t.name }}
      </div>
      <div style="width: 250px">
        {{ PROGRAM[t.program] }}
      </div>
      <div style="width: 150px">
        {{ t.minutes }} минут
      </div>
      <div>
        {{ plural(t.questions_count, ["вопрос", "вопроса", "вопросов"]) }}
      </div>
      <div class="table-actions">
        <router-link
          v-if="t.is_finished"
          :to="{ name: 'tests-result-id', params: { id: t.id } }"
        >
          {{ formatClientTestResults(t) }}
          баллов
        </router-link>
        <template v-else>
          <span
            v-if="user?.entity_type === ENTITY_TYPE.user"
            class="text-gray"
          >
            не пройден
          </span>
          <v-btn
            v-else
            color="primary"
            :to="{
              name: 'tests-start-id',
              params: {
                id: t.id,
              },
            }"
          >
            начать тест
          </v-btn>
        </template>
      </div>
    </div>
  </div>
</template>
