<script lang="ts" setup>
import type { RequestStatus, Program } from '~/utils/models'
import { REQUEST_STATUS, PROGRAM } from '~/utils/sment'

const { dialog, width } = useDialog()
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

function open() {
  dialog.value = true
}

function apply() {
  dialog.value = false
  emit('apply', filters.value)
}

function clearStatus() {
  filters.value.status = undefined
  nextTick(() => statusInput.value.blur())
}

function clearProgram() {
  filters.value.program = undefined
  nextTick(() => programInput.value.blur())
}

const count = computed(
  () => Object.values(filters.value).filter(e => e !== undefined).length,
)

const emit = defineEmits<{
  (e: 'apply', filters: RequestFilters): void
}>()

defineExpose({ open })
</script>

<template>
  <v-dialog
    v-model="dialog"
    :width="width"
  >
    <div class="dialog-wrapper">
      <div class="dialog-header">
        <div>
          Фильтры
          <span
            v-if="count"
            class="ml-1 text-gray"
          >
            {{ count }}
          </span>
        </div>
        <v-btn
          icon="$save"
          :size="48"
          color="#fafafa"
          @click="apply()"
        />
      </div>
      <div class="dialog-body">
        <div>
          <v-select
            ref="statusInput"
            v-model="filters.status"
            label="Статус"
            :items="statuses"
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
          <div>
            <v-select
              ref="programInput"
              v-model="filters.program"
              :items="programs"
              label="Программа"
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
