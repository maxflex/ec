<script setup lang="ts">
import type { StatsMetricsEditor } from '#build/components'
import { mdiChevronRight, mdiPlus } from '@mdi/js'
import { clone } from 'rambda'
import { VueDraggableNext } from 'vue-draggable-next'
import {
  type MetricComponent,
  MetricComponents,
  type StatsMetric,
  type StatsParams,
  type StatsPreset,
} from '~/components/Stats/Metrics'

const emit = defineEmits<{
  go: [params: StatsParams]
}>()

const { dialog, width } = useDialog('large')
const presetDialog = ref()

const params = ref<StatsParams>({
  metrics: [],
  mode: 'day',
  date: null,
})

const metricsEditor = ref<InstanceType<typeof StatsMetricsEditor>>()
const selected = ref<number>()
const presets = ref<StatsPreset[]>([])

function open() {
  dialog.value = true
  selected.value = undefined
  loadPresets()
}

function go() {
  dialog.value = false
  emit('go', params.value)
}

function addMetric(metric: MetricComponent) {
  const id = newId()
  params.value.metrics.push({
    id,
    metric,
    label: MetricComponents[metric].label,
    filters: MetricComponents[metric].filters,
    color: 'black',
  })
  // itemUpdated('metric', id)
}

// Загрузить конфигурацию из пресета
function loadFromPreset(preset: StatsPreset) {
  params.value = clone(preset.params)
  selected.value = undefined
  // preset.params.metrics.map(m => itemUpdated('metric', m.id))
}

function deletePreset(preset: StatsPreset) {
  presets.value.splice(
    presets.value.findIndex(e => e.id === preset.id),
    1,
  )
  useHttp(`stats-presets/${preset.id}`, {
    method: 'delete',
  })
}

async function loadPresets() {
  const { data } = await useHttp<StatsPreset[]>(`stats-presets`)
  presets.value = data.value!
}

function onMetricUpdated(m: StatsMetric) {
  const i = params.value.metrics.findIndex(e => e.id === selected.value)
  params.value.metrics.splice(i, 1, m)
}

function onPresetSave(p: StatsPreset) {
  presets.value.push(p)
  itemUpdated('stats-preset', p.id)
}

function deleteMetric(m: StatsMetric) {
  const i = params.value.metrics.findIndex(e => e.id === m.id)
  if (m.id === selected.value) {
    selected.value = undefined
  }
  params.value.metrics.splice(i, 1)
}

function editMetric(m: StatsMetric) {
  selected.value = m.id
  nextTick(() => metricsEditor.value?.open(m))
}

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-wrapper">
      <div class="dialog-header">
        Конфигуратор

        <div class="d-flex ga-4">
          <v-btn variant="text" @click="presetDialog?.open(params)">
            сохранить пресет
          </v-btn>
          <v-btn color="primary" @click="go()">
            отобразить
          </v-btn>
        </div>
        <!--        <v-btn :size="48" icon="$save" variant="text" @click="save()" /> -->
      </div>
      <div class="dialog-body pa-0">
        <div class="stats-dialog">
          <div>
            <div class="stats-dialog__inputs">
              <div>
                <v-select
                  v-model="params.mode"
                  :items="selectItems(StatsModeLabel)"
                  label="Отображать"
                />
              </div>
              <div>
                <UiDateInput
                  v-model="params.date"
                  label="Начиная с"
                  today-btn
                  clearable
                  placeholder="текущего дня"
                />
              </div>
            </div>
            <v-table v-if="presets.length > 0" class="mb-8" hover>
              <thead>
                <tr>
                  <th>Пресеты</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="preset in presets"
                  :id="`stats-preset-${preset.id}`"
                  :key="preset.id"
                  @click="loadFromPreset(preset)"
                >
                  <td>
                    <div class="stats-dialog__metric">
                      <span>
                        {{ preset.name }}
                      </span>
                      <div class="d-flex ga-1 align-center">
                        <v-icon
                          icon="$delete"
                          color="gray"
                          :size="22"
                          class="stats-dialog__remove-preset"
                          @click.stop="deletePreset(preset)"
                        />
                      </div>
                    </div>
                  </td>
                </tr>
              </tbody>
            </v-table>
            <v-table hover>
              <thead>
                <tr>
                  <th>
                    Метрики
                  </th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(metric, key) in MetricComponents" :key="key" @click="addMetric(key)">
                  <td>
                    <div class="stats-dialog__metric">
                      <span>
                        {{ metric.label }}
                      </span>
                      <v-icon :icon="mdiPlus" color="gray" />
                    </div>
                  </td>
                </tr>
              </tbody>
            </v-table>
          </div>
          <div>
            <v-table hover>
              <thead>
                <tr>
                  <th>
                    Добавленные
                  </th>
                </tr>
              </thead>

              <VueDraggableNext
                v-model="params.metrics"
                :remove-clone-on-hide="true"
                :animation="200"
                direction="vertical"
                tag="tbody"
              >
                <tr
                  v-for="m in params.metrics"
                  :id="`metric-${m.id}`"
                  :key="m.id"
                  :class="{ 'stats-dialog__selected-metric': m.id === selected }"
                  @click="editMetric(m)"
                >
                  <td>
                    <div class="stats-dialog__metric">
                      <span>
                        {{ m.label }}
                      </span>
                      <div class="d-flex ga-1 align-center">
                        <v-icon
                          icon="$delete" color="gray" :size="22"
                          class="stats-dialog__remove-preset"
                          @click.stop="deleteMetric(m)"
                        />
                        <v-icon :icon="mdiChevronRight" color="gray" />
                      </div>
                    </div>
                  </td>
                </tr>
              </VueDraggableNext>
            </v-table>
          </div>
          <div class="stats-dialog__inputs">
            <StatsMetricsEditor
              v-if="selected !== undefined"
              ref="metricsEditor"
              @update="onMetricUpdated"
            />
          </div>
        </div>
      </div>
    </div>
  </v-dialog>
  <StatsPresetDialog ref="presetDialog" @save="onPresetSave" />
</template>

<style lang="scss">
.stats-dialog {
  display: flex;
  flex: 1;

  &__inputs {
    display: flex;
    flex-direction: column;
    gap: 30px;
    padding: 30px 20px;
  }

  & > div {
    flex: 1;
    max-height: calc(100vh - 64px);
    overflow: scroll;
    padding-bottom: 30px;
    &::-webkit-scrollbar {
      display: none; /* Safari and Chrome */
    }
    &:not(:last-child) {
      border-right: 1px solid rgb(var(--v-theme-border));
    }
  }

  .v-table {
    left: 0 !important;
    width: auto !important;
    min-width: auto !important;
    tbody:has(tr.sortable-chosen) {
      tr:not(.stats-dialog__selected-metric) {
        td {
          background: white !important;
        }
      }
    }
    th {
      text-align: center !important;
      cursor: default !important;
    }
    tr {
      cursor: pointer;
      user-select: none;
      .v-icon {
        transition: opacity 0.28s cubic-bezier(0.4, 0, 0.2, 1);
        opacity: 0.5;
      }
      &:active td {
        background: white !important;
      }
      &:last-child {
        td {
          border-bottom: thin solid rgb(var(--v-theme-border));
        }
      }
      &:hover {
        .v-icon:not(.stats-dialog__remove-preset):last-child {
          opacity: 1;
        }
      }
    }
  }
  &__metric {
    display: flex;
    justify-content: space-between;
  }
  &__selected-metric {
    td {
      //background: rgba(var(--v-theme-primary), 0.1) !important;
      //background: #fff3d6 !important;
      background: #f5f5f5 !important;
    }
    .v-icon:last-child {
      opacity: 1 !important;
    }
  }
  &__remove-preset {
    &:hover {
      color: rgb(var(--v-theme-error)) !important;
    }
  }
}
</style>
