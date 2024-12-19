<script setup lang="ts">
import type { StatsMetricsEditor } from '#build/components'
import { mdiChevronRight, mdiPlus } from '@mdi/js'
import { type MetricComponent, MetricComponents, type StatsMetric, type StatsParams, type StatsPreset } from '~/components/Stats/Metrics'

const emit = defineEmits<{
  save: [params: StatsParams]
}>()

const { dialog, width } = useDialog('x-large')

const params = ref<StatsParams>({
  metrics: [],
  mode: 'day',
  date: today(),
})

const metricsEditor = ref<InstanceType<typeof StatsMetricsEditor>>()
const selectedIndex = ref(-1)
const presets = ref<StatsPreset[]>([])

function open() {
  dialog.value = true
  loadPresets()
}

function save() {
  dialog.value = false
  emit('save', params.value)
}

function addMetric(metric: MetricComponent) {
  params.value.metrics.push({
    metric,
    label: MetricComponents[metric].label,
    filters: MetricComponents[metric].filters,
    color: 'black',
  })
  // itemUpdated('metric', selected.value.length - 1)
}

function addPreset(preset: StatsPreset) {
  const { metric, label, filters, color } = preset
  params.value.metrics.push({ metric, label, filters, color })
}

function deletePreset(preset: StatsPreset) {
  if (!confirm(`Удалить ${preset.label}?`)) {
    return
  }
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

function onMetricSave(m: StatsMetric) {
  const i = selectedIndex.value
  selectedIndex.value = -1
  params.value.metrics.splice(i, 1, m)
  itemUpdated('metric', i)
}

function onPresetSave(m: StatsPreset) {
  presets.value.push(m)
  itemUpdated('stats-preset', m.id)
}

function onMetricDelete() {
  const i = selectedIndex.value
  selectedIndex.value = -1
  params.value.metrics.splice(i, 1)
}

function editMetric(m: StatsMetric, i: number) {
  selectedIndex.value = i
  nextTick(() => metricsEditor.value?.open(m))
}

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-wrapper">
      <div class="dialog-header">
        Конфигуратор
        <v-btn color="primary" @click="save()">
          отобразить
        </v-btn>
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
                />
              </div>
            </div>
            <v-table hover>
              <thead>
                <tr>
                  <th>
                    Добавить метрики
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
                <tr
                  v-for="preset in presets"
                  :id="`stats-preset-${preset.id}`"
                  :key="preset.id"
                  @click="addPreset(preset)"
                >
                  <td>
                    <div class="stats-dialog__metric">
                      <span>
                        {{ preset.label }}
                      </span>
                      <div class="d-flex ga-1 align-center">
                        <v-icon
                          icon="$delete" color="gray" :size="22"
                          class="stats-dialog__remove-preset"
                          @click.stop="deletePreset(preset)"
                        />
                        <v-icon :icon="mdiPlus" color="gray" />
                      </div>
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
              <tbody>
                <tr
                  v-for="(m, index) in params.metrics"
                  :id="`metric-${index}`"
                  :key="index"
                  :class="{ 'stats-dialog__selected-metric': index === selectedIndex }"
                  @click="editMetric(m, index)"
                >
                  <td>
                    <div class="stats-dialog__metric">
                      <span>
                        {{ m.label }}
                      </span>
                      <v-icon :icon="mdiChevronRight" color="gray" />
                    </div>
                  </td>
                </tr>
              </tbody>
            </v-table>
          </div>
          <div class="stats-dialog__inputs">
            <StatsMetricsEditor
              v-if="selectedIndex >= 0"
              ref="metricsEditor"
              @save="onMetricSave"
              @save-preset="onPresetSave"
              @delete="onMetricDelete"
            />
          </div>
        </div>
      </div>
    </div>
  </v-dialog>
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
    max-height: calc(100vh - 64px);
    overflow: scroll;
  }

  & > div {
    flex: 1;
    &:not(:last-child) {
      border-right: 1px solid rgb(var(--v-theme-border));
    }
  }

  .v-table {
    left: 0 !important;
    width: auto !important;
    min-width: auto !important;
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
      &:last-child {
        td {
          border-bottom: thin solid rgb(var(--v-theme-border));
        }
      }
      &:hover {
        .v-icon {
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
    .v-icon {
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
