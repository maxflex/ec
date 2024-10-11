<script lang="ts" setup>
import Metrics from '~/components/Stats/Metrics'

const emit = defineEmits<{
  select: [items: StatsMetric[]]
}>()

const { dialog, width } = useDialog('default')
const selected = ref<StatsMetric[]>([])

function open() {
  dialog.value = true
  selected.value = []
}

function onSelect(key: StatsMetric) {
  const index = selected.value.findIndex(k => k === key)
  index === -1
    ? selected.value.push(key)
    : selected.value.splice(index, 1)
}

function save() {
  emit('select', selected.value)
  dialog.value = false
}

defineExpose({ open })
</script>

<template>
  <v-dialog v-model="dialog" :width="width">
    <div class="dialog-wrapper">
      <div class="dialog-header">
        Добавить метрики
        <v-btn :size="48" icon="$save" variant="text" @click="save()" />
      </div>
      <div class="dialog-body pt-0">
        <div class="table table--hover table-metrics-dialog">
          <div v-for="(metric, key) in Metrics" :key="key" @click="onSelect(key)">
            <div>
              <div class="vfn-1" style="width: 20px">
                <UiCheckbox :value="selected.includes(key)" />
              </div>
              {{ metric.label }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </v-dialog>
</template>

<style lang="scss" scoped>
.table-metrics-dialog {
  & > div {
    cursor: pointer;
    user-select: none;
    & > div:first-child {
      display: flex;
      gap: 16px;
    }
  }
}
</style>
