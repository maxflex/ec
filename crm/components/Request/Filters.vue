<script lang="ts" setup>
import type { RequestStatus, Program } from '~/utils/models'
import { REQUEST_STATUS, PROGRAM } from '~/utils/sment'

const statusInput = ref()
const programInput = ref()

const filters = ref<RequestFilters>({})

const statuses = Object.keys(REQUEST_STATUS).map(value => ({
  value,
  title: REQUEST_STATUS[value as RequestStatus],
}))

const programs = Object.keys(PROGRAM).map(value => ({
  value,
  title: PROGRAM[value as Program],
}))

// function apply() {
//   emit('apply', filters.value)
// }

function clearStatus() {
  filters.value.status = undefined
  nextTick(() => statusInput.value.blur())
}

function clearProgram() {
  filters.value.program = undefined
  nextTick(() => programInput.value.blur())
}

watch(filters.value, () => emit('apply', filters.value))

// const count = computed(
//   () => Object.values(filters.value).filter(e => e !== undefined).length,
// )

const emit = defineEmits<{
  (e: 'apply', filters: RequestFilters): void
}>()

defineExpose({ open })
</script>

<template>
  <div class="filters-inputs">
    <div>
      <v-select
        ref="statusInput"
        v-model="filters.status"
        label="Статус"
        :items="statuses"
        density="comfortable"
        :menu-props="{
          closeOnContentClick: true,
        }"
      >
        <template #prepend-item>
          <v-list-item
            base-color="gray"
            @click="clearStatus()"
          >
            <v-list-item-title class="gray">
              не установлено
            </v-list-item-title>
            <template #prepend>
              <v-spacer />
            </template>
          </v-list-item>
        </template>
      </v-select>
    </div>
    <div>
      <v-select
        ref="programInput"
        v-model="filters.program"
        :items="programs"
        label="Программа"
        density="comfortable"
      >
        <template #prepend-item>
          <v-list-item
            base-color="gray"
            @click="clearProgram()"
          >
            <v-list-item-title class="gray">
              не установлено
            </v-list-item-title>
            <template #prepend>
              <v-spacer />
            </template>
          </v-list-item>
        </template>
      </v-select>
    </div>
    <!-- <div>
          <UiDateInput v-model="filters.date" />
        </div> -->
    <!-- <div>
          {{ filters }}
        </div> -->
  </div>
</template>
