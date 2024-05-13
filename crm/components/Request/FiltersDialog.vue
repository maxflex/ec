<script lang="ts" setup>
import type { RequestStatus, Program } from "~/utils/models"
import { REQUEST_STATUS, PROGRAM } from "~/utils/sment"

const { dialog, width } = useDialog()
const statusInput = ref()
const programInput = ref()

const filters = ref<RequestFilters>({})

const statuses = Object.keys(REQUEST_STATUS).map((value) => ({
  value,
  title: REQUEST_STATUS[value as RequestStatus],
}))

const programs = Object.keys(PROGRAM).map((value) => ({
  value,
  title: PROGRAM[value as Program],
}))

function open() {
  dialog.value = true
}

function apply() {
  dialog.value = false
  emit("apply", filters.value)
}

function clearStatus() {
  filters.value.status = undefined
  nextTick(() => statusInput.value.blur())
}

function clearProgram() {
  filters.value.program = undefined
  nextTick(() => programInput.value.blur())
}

// const count = computed(() => Object.keys(filters.value).length)

const emit = defineEmits<{
  (e: "apply", filters: RequestFilters): void
}>()

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-wrapper">
      <div class="dialog-header">
        <div>
          Фильтры
          <!-- <span class="ml-1 text-gray" v-if="count">
            {{ count }}
          </span> -->
        </div>
        <v-btn icon="$save" :size="48" @click="apply()" color="#fafafa" />
      </div>
      <div class="dialog-body">
        <div>
          <v-select
            label="Статус"
            v-model="filters.status"
            :items="statuses"
            :menu-props="{
              closeOnContentClick: true,
            }"
            ref="statusInput"
          >
            <template #prepend-item>
              <v-list-item @click="clearStatus()" base-color="gray">
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
          <div>
            <v-select
              :items="programs"
              v-model="filters.program"
              label="Программа"
              ref="programInput"
            >
              <template #prepend-item>
                <v-list-item @click="clearProgram()" base-color="gray">
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
        </div>
        <!-- <div>
          <UiDateInput v-model="filters.date" />
        </div> -->
        <!-- <div>
          {{ filters }}
        </div> -->
      </div>
    </div>
  </v-dialog>
</template>
