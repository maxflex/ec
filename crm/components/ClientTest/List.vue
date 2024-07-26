<script setup lang="ts">
import { plural } from '~/utils/filters'

const { items } = defineProps<{ items: ClientTestResource[] }>()
const { user } = useAuthStore()
</script>

<template>
  <div class="table table--hover">
    <div
      v-for="t in items"
      :id="`client-test-${t.id}`"
      :key="t.id"
    >
      <div style="width: 250px">
        {{ t.name }}
      </div>
      <div style="width: 130px">
        {{ ProgramShortLabel[t.program] }}
      </div>
      <div style="width: 130px">
        {{ t.minutes }} минут
      </div>
      <div style="flex: 1">
        {{ plural(t.questions_count, ["вопрос", "вопроса", "вопросов"]) }}
      </div>
      <template v-if="user?.entity_type === EntityType.user">
        <div style="width: 140px; flex: initial" class="text-gray">
          {{ formatDateTime(t.created_at) }}
        </div>
      </template>
      <template v-else>
        <v-btn
          color="primary"
          density="comfortable"
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
      <!-- <div class="table-actions">
        <router-link
          v-if="t.is_finished"
          :to="{ name: 'tests-result-id', params: { id: t.id } }"
        >
          {{ formatClientTestResults(t) }}
          баллов
        </router-link>
        <template v-else>
          <span
            v-if="user?.entity_type === EntityType.user"
            class="text-gray"
          >
            не пройден
          </span>

        </template>
      </div> -->
    </div>
  </div>
</template>
