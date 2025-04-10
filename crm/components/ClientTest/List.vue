<script setup lang="ts">
import type { ClientTestResource } from '.'

const { items } = defineProps<{ items: ClientTestResource[] }>()

const emit = defineEmits<{
  destroy: [t: ClientTestResource]
}>()

// isTeacher – for head teachers
const { isAdmin, isClient } = useAuthStore()
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
      <div style="width: 230px; flex: 1">
        <div v-if="t.client">
          <NuxtLink :to="{ name: 'clients-id', params: { id: t.client.id } }">
            {{ formatName(t.client) }}
          </NuxtLink>
        </div>
        {{ t.name }}
      </div>
      <div style="width: 130px">
        {{ ProgramShortLabel[t.program] }}
      </div>
      <div style="width: 130px">
        <div>
          {{ t.minutes }} минут
        </div>
        <div>
          {{ plural(t.questions_count, ["вопрос", "вопроса", "вопросов"]) }}
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
          {{ t.results.score }} из {{ t.results.total }}
        </b>
        <ClientTestCountDown v-else-if="t.is_active" :item="t" />
      </div>
      <div class="text-right">
        <v-btn
          v-if="t.is_finished"
          color="secondary"
          density="comfortable"
          variant="tonal"
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
        <v-btn
          v-else-if="isClient"
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
