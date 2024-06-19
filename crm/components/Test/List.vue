<script setup lang="ts">
import type { Test, Tests } from '~/utils/models'
import { PROGRAM } from '~/utils/sment'
import { plural } from '~/utils/filters'

const { tests } = defineProps<{ tests: Tests }>()
const emit = defineEmits<{ (e: 'open', test: Test): void }>()
const selected = defineModel<Tests>('selected')
const selectable = selected.value !== undefined

function select(t: Test) {
  if (!selected.value) {
    return
  }
  const index = selected.value.findIndex(({ id }) => id === t.id)
  index !== -1 ? selected.value.splice(index, 1) : selected.value.push(t)
}
</script>

<template>
  <div class="table table--hover">
    <div
      v-for="t in tests"
      :key="t.id"
      :class="{ 'cursor-pointer': selectable }"
      @click="select(t)"
    >
      <div v-if="selected">
        <v-icon
          v-if="selected.some(({ id }) => id === t.id)"
          color="secondary"
          icon="$checkboxOn"
        />
        <v-icon
          v-else
          icon="$checkboxOff"
        />
      </div>
      <div style="width: 220px">
        {{ t.name }}
        <!-- <NuxtLink :to="{ name: 'tests-id', params: { id: t.id } }">
          {{ t.name }}
        </NuxtLink> -->
      </div>
      <div style="width: 250px">
        <template v-if="t.program">
          {{ PROGRAM[t.program] }}
        </template>
        <span
          v-else
          class="text-gray"
        > не установлено </span>
      </div>
      <div style="width: 150px">
        {{ t.minutes }} минут
      </div>
      <div>
        <template v-if="t.questions?.length">
          {{ plural(t.questions.length, ["вопрос", "вопроса", "вопросов"]) }}
        </template>
        <span
          v-else
          class="text-gray"
        > нет вопросов </span>
      </div>
      <div
        v-if="!selectable"
        class="table-actions"
      >
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
