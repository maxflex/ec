<script setup lang="ts">
import type { StatsMetricsEditor } from '#build/components'
import { mdiEyeOffOutline, mdiPlus } from '@mdi/js'
import { clone } from 'lodash-es'
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
const savePresetDialog = ref()

const params = ref<StatsParams>({
  metrics: [],
  mode: 'day',
  date_from: null,
  date_to: null,
})

const showPresets = ref(false)
const metricsEditor = ref<InstanceType<typeof StatsMetricsEditor>>()
const selected = ref<number>()
const presets = ref<StatsPreset[]>([])
const isBackButton = ref(false)

function open(hasData: boolean = false) {
  isBackButton.value = hasData
  dialog.value = true
  selected.value = undefined
  loadPresets()
}

function go() {
  if (params.value.metrics.length === 0) {
    return
  }
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
  showPresets.value = true
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
provide<Ref<StatsParams>>('params', params)
</script>

<template>
  <v-dialog v-model="dialog" :width="width" class="dialog-fullwidth">
    <div class="dialog-wrapper">
      <div class="dialog-header pl-5">
        <div class="d-flex align-center ga-2">
          <v-btn v-if="isBackButton" icon="$back" :size="48" @click="dialog = false" />
          Конфигуратор
        </div>
        <div class="d-flex ga-4">
          <v-btn variant="text" @click="savePresetDialog?.open(params)">
            сохранить пресет
          </v-btn>
          <v-btn color="primary" @click="go()">
            отобразить
          </v-btn>
        </div>
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
              <div class="double-input-glued">
                <UiDateInput
                  v-model="params.date_to"
                  label="Начиная с"
                  today-btn
                  clearable
                  placeholder="текущего дня"
                  fullscreen
                />
                <UiDateInput
                  v-model="params.date_from"
                  fullscreen
                  label="по"
                  clearable
                  placeholder="год назад"
                />
              </div>
              <div class="d-flex ga-3">
                <v-btn :color="!showPresets ? 'primary' : undefined" style="flex: 1" @click="showPresets = false">
                  метрики
                </v-btn>
                <v-btn :color="showPresets ? 'primary' : undefined" style="flex: 1" @click="showPresets = true">
                  пресеты
                </v-btn>
              </div>
            </div>
            <v-table v-if="!showPresets" hover>
              <tbody>
                <tr
                  v-for="(metric, key) in MetricComponents"
                  :key="key"
                  @click="addMetric(key)"
                >
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
            <v-table v-else-if="presets.length > 0" hover>
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
                      <v-icon
                        :icon="mdiPlus"
                        color="gray"
                        class="stats-dialog__remove"
                        @click.stop="deletePreset(preset)"
                      />
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
                          v-if="m.hidden"
                          :icon="mdiEyeOffOutline"
                          color="gray"
                          :size="20"
                        />
                        <v-icon
                          :icon="mdiPlus"
                          color="gray"
                          class="stats-dialog__remove"
                          @click.stop="deleteMetric(m)"
                        />
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
  <StatsSavePresetDialog ref="savePresetDialog" @save="onPresetSave" />
</template>

<style lang="scss">
.stats-dialog {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  flex: 1;

  &__inputs {
    display: flex;
    flex-direction: column;
    gap: 30px;
    padding: 30px 20px;
  }

  & > div {
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
        .v-icon:not(.stats-dialog__remove):last-child {
          opacity: 1;
        }
      }
    }
  }
  &__metric {
    display: flex;
    justify-content: space-between;
    &--disabled {
      opacity: 0.5;
      pointer-events: none;
    }
  }
  &__selected-metric {
    td {
      //background: rgba(var(--v-theme-primary), 0.1) !important;
      //background: #fff3d6 !important;
      background: #f5f5f5 !important;
    }
  }
  &__remove {
    transform: rotate(45deg);
    &:hover {
      color: rgb(var(--v-theme-error)) !important;
    }
  }
}
</style>
