<script setup lang="ts">
import type { Test, Tests } from "~/utils/models"
import { PROGRAM } from "~/utils/sment"
import { plural } from "~/utils/filters"

const { tests, selectable } = defineProps<{
  tests: Tests
  selectable: boolean
}>()

const selected = ref<Tests>([])

const emit = defineEmits<{
  (e: "open", test: Test): void
}>()

function select(t: Test) {
  const index = selected.value.findIndex((e) => e === t)
  index !== -1 ? selected.value.splice(index, 1) : selected.value.push(t)
}
</script>

<template>
  <div class="table table--hover">
    <div
      v-for="t in tests"
      :key="t.id"
      @click="select(t)"
      :class="{ 'cursor-pointer': selectable }"
    >
      <div v-if="selectable">
        <v-icon
          v-if="selected.includes(t)"
          color="primary"
          icon="$checkboxOn"
        />
        <v-icon v-else icon="$checkboxOff" />
      </div>
      <div style="width: 220px">
        <NuxtLink :to="{ name: 'tests-id', params: { id: t.id } }">
          {{ t.name }}
        </NuxtLink>
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
      <div class="table-actions" v-if="!selectable">
        <v-btn
          variant="text"
          icon="$more"
          :size="48"
          @click="emit('open', t)"
        />
      </div>
    </div>
  </div>
</template>
