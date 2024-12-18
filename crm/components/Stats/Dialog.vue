<script setup lang="ts">
import type { StatsMetricsDialog } from '#build/components'
import { mdiChevronRight, mdiPlus } from '@mdi/js'
import { type MetricComponent, MetricComponents, type StatsMetric, type StatsParams } from '~/components/Stats/Metrics'

const emit = defineEmits<{
  save: [params: StatsParams]
}>()

const { dialog, width } = useDialog('large')

const params = ref<StatsParams>({
  metrics: [],
  mode: 'day',
  date: today(),
})

const metricsDialog = ref<InstanceType<typeof StatsMetricsDialog>>()

function open() {
  dialog.value = true
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

function onMetricSave(index: number, m: StatsMetric) {
  params.value.metrics.splice(index, 1, m)
  itemUpdated('metric', index)
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
          <div>
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
                    <div class="d-flex align-center justify-space-between">
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
              <tbody>
                <tr
                  v-for="(m, index) in params.metrics"
                  :id="`metric-${index}`"
                  :key="index"
                  @click="metricsDialog?.open(m, index)"
                >
                  <td>
                    <div class="d-flex align-center justify-space-between">
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
        </div>
      </div>
    </div>
  </v-dialog>
  <StatsMetricsDialog ref="metricsDialog" @save="onMetricSave" />
</template>

<style lang="scss">
.stats-dialog {
  display: flex;
  flex: 1;

  & > div {
    flex: 1;
    &:not(:last-child) {
      border-right: 1px solid rgb(var(--v-theme-border));
    }
    &:first-child {
      padding: 30px 26px;
      display: flex;
      flex-direction: column;
      gap: 30px;
      flex: 0.8;
      //background: rgb(var(--v-theme-bg));
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
}
</style>
