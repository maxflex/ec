<script setup lang="ts">
import { plural } from '~/utils/filters'

const { items } = defineProps<{ items: ClientTestResource[] }>()

const emit = defineEmits<{
  destroy: [t: ClientTestResource]
}>()

const { user } = useAuthStore()
</script>

<template>
  <div class="table table--padding">
    <div
      v-for="t in items"
      :id="`client-test-${t.id}`"
      :key="t.id"
    >
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
        <router-link
          v-if="t.is_finished"
          :to="{ name: 'tests-result-id', params: { id: t.id } }"
        >
          {{ formatClientTestResults(t) }}
        </router-link>
        <ClientTestCountDown v-else-if="t.is_active" :item="t" />
      </div>
      <!-- ADMIN -->
      <template v-if="user?.entity_type === EntityType.user">
        <div v-if="!t.is_active && !t.is_finished" class="table-actionss" style="top: 12px">
          <v-btn
            icon="$close"
            :size="48"
            variant="plain"
            color="red"
            @click="emit('destroy', t)"
          />
        </div>
        <div style="width: 140px; flex: initial" class="text-gray">
          {{ formatDateTime(t.created_at) }}
        </div>
      </template>
      <!-- CLIENT -->
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
    </div>
  </div>
</template>
