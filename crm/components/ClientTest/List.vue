<script setup lang="ts">
import type { ClientTestResource } from '.'

const { items, showClient } = defineProps<{
  items: ClientTestResource[]
  showClient?: boolean
}>()

const emit = defineEmits<{
  timeout: []
  destroy: [t: ClientTestResource]
}>()

// isTeacher – for head teachers
const { isAdmin, isStudent } = useAuthStore()
</script>

<template>
  <div class="table table--padding">
    <div
      v-for="t in items"
      :id="`client-test-${t.id}`"
      :key="t.id"
    >
      <div v-if="isAdmin && !t.is_active" class="table-actionss d-flex align-center justify-end">
        <v-btn
          icon="$close"
          :size="48"
          color="error"
          variant="plain"
          @click="emit('destroy', t)"
        />
      </div>
      <div v-if="showClient" style="width: 200px">
        <NuxtLink :to="{ name: 'clients-id', params: { id: t.client.id } }">
          {{ formatName(t.client) }}
        </NuxtLink>
      </div>
      <div style="width: 230px; flex: 1">
        {{ t.name }}
        <div v-if="t.description">
          {{ t.description }}
        </div>
      </div>
      <div style="width: 130px">
        <div>
          {{ t.minutes }} минут
        </div>
        <div>
          {{ plural(t.question_counts.length, ["вопрос", "вопроса", "вопросов"]) }}
        </div>
      </div>
      <div style="width: 250px">
        <div v-if="t.started_at">
          начат
          <span>
            {{ formatDateTime(t.started_at) }}
          </span>
        </div>
        <div v-if="t.is_finished">
          <span v-if="t.finished_at">
            завершен
            {{ formatDateTime(t.finished_at) }}
          </span>
          <span v-else class="text-error">
            время истекло
          </span>
        </div>
        <div v-else-if="t.is_active" class="text-success">
          активен
        </div>
        <div v-else class="text-gray">
          не пройден
        </div>
      </div>
      <div style="width: 100px">
        <b v-if="t.results">
          <template v-if="t.finished_at">
            {{ t.results.score }} из {{ t.results.total }}
          </template>
        </b>
        <UiCountDown v-else-if="t.is_active" :seconds="t.seconds_left!" class="font-weight-bold" @timeout="emit('timeout')" />
      </div>
      <div style="width: 180px; flex: initial">
        <template v-if="t.is_finished">
          <v-btn
            v-if="t.finished_at"
            color="secondary"
            density="comfortable"
            variant="tonal"
            class="tests-result-id"
            :width="154"
            :to="{
              name: 'tests-result-id',
              params: {
                id: t.id,
              },
            }"
          >
            просмотр
          </v-btn>
        </template>
        <v-btn
          v-else-if="isStudent"
          color="primary"
          density="comfortable"
          :width="154"
          :to="{
            name: 'tests-start-id',
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
