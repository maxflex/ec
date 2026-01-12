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
const { isAdmin, isClient } = useAuthStore()
</script>

<template>
  <Table class="table--padding">
    <TableRow
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
      <TableCol v-if="showClient" :width="200">
        <NuxtLink :to="{ name: 'clients-id', params: { id: t.client.id } }">
          {{ formatName(t.client) }}
        </NuxtLink>
      </TableCol>
      <TableCol style="width: 230px">
        {{ t.name }}
        <div v-if="t.description">
          {{ t.description }}
        </div>
      </TableCol>
      <TableCol :width="130">
        <div>
          {{ t.minutes }} минут
        </div>
        <div>
          {{ plural(t.question_counts.length, ["вопрос", "вопроса", "вопросов"]) }}
        </div>
      </TableCol>
      <TableCol :width="250">
        <div v-if="isAdmin">
          создан {{ formatDateTime(t.created_at) }}
        </div>
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
      </TableCol>
      <TableCol :width="100">
        <b v-if="t.results">
          <template v-if="t.finished_at">
            {{ t.results.score }} из {{ t.results.total }}
          </template>
        </b>
        <UiCountDown v-else-if="t.is_active" :seconds="t.seconds_left!" class="font-weight-bold" @timeout="emit('timeout')" />
      </TableCol>
      <TableCol style="width: 180px; flex: initial">
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
        <v-btn
          v-else
          color="secondary"
          density="comfortable"
          variant="tonal"
          class="tests-result-id"
          :width="154"
          :to="{
            name: 'tests-id',
            params: { id: t.id },
          }"
        >
          просмотр
        </v-btn>
      </TableCol>
    </TableRow>
  </Table>
</template>
