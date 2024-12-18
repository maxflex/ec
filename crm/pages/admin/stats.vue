<script lang="ts" setup>
import type { StatsDialog } from '#build/components'
import { mdiTune } from '@mdi/js'
import { clone } from 'rambda'
import { VueDraggableNext } from 'vue-draggable-next'
import { MetricComponents, type StatsMetric, type StatsParams } from '~/components/Stats/Metrics'

const statsDialog = ref<InstanceType<typeof StatsDialog>>()

const params = ref<StatsParams>({
  metrics: [],
  mode: 'day',
  date: today(),
})

// сохраняем параметры ответа сервера, чтобы не зависеть от текущих параметров
// (например, если снесли метрику или изменили режим на "по годам", чтобы не менялось форматирование)
const responseParams = ref<StatsParams>()

const loading = ref(false)

const items = ref<StatsListResource[]>([])
const page = ref<number>(0)

const isDragging = ref(false)
const isOverDeleteThreshold = ref(false)
const draggedIndex = ref(0)
let dragStartY: number = 0
const dragThreshold = 300

let scrollContainer: HTMLElement | null = null

function onSave(p: StatsParams) {
  params.value = clone(p)
  loadData()
}

function deleteMetric(index: number) {
  params.value.metrics.splice(index, 1)
}

async function loadMore() {
  if (loading.value) {
    return
  }
  loading.value = true
  page.value++
  const { data } = await useHttp<StatsListResource[]>(
    `stats`,
    {
      method: 'post',
      body: {
        ...params.value,
        page: page.value,
      },
    },
  )
  if (data.value) {
    if (page.value === 1) {
      responseParams.value = clone(params.value)
      items.value = data.value
    }
    else {
      items.value = items.value.concat(data.value)
    }
  }
  loading.value = false
}

function loadData() {
  page.value = 0
  if (scrollContainer) {
    scrollContainer.scrollTop = 0
  }
  loadMore()
}

function getWidth(m: StatsMetric) {
  const { width } = MetricComponents[m.metric]
  return {
    width: `${width || 120}px`,
  }
}

function onScroll() {
  if (!scrollContainer || loading.value || !items.value.length) {
    return
  }
  const { scrollTop, scrollHeight, clientHeight } = scrollContainer
  const scrollPosition = scrollTop + clientHeight
  const scrollThreshold = scrollHeight * 0.9

  if (scrollPosition >= scrollThreshold) {
    loadMore()
  }
}

onMounted(() => {
  scrollContainer = document.documentElement.querySelector('main')
  scrollContainer?.addEventListener('scroll', onScroll)
})

onUnmounted(() => {
  scrollContainer?.removeEventListener('scroll', onScroll)
})

// Methods
function onDragStart(evt: any) {
  isDragging.value = true
  dragStartY = (evt.originalEvent as DragEvent).clientY
  draggedIndex.value = evt.oldIndex
  isOverDeleteThreshold.value = false
}

function onDragging(evt: DragEvent) {
  const { x, y, clientX, clientY } = evt

  // Это dragEnd
  if (x === 0 && y === 0 && clientX === 0 && clientY === 0) {
    if (isOverDeleteThreshold.value) {
      deleteMetric(draggedIndex.value)
    }
    isOverDeleteThreshold.value = false
    return
  }

  const distanceDragged = clientY - dragStartY
  isOverDeleteThreshold.value = distanceDragged > dragThreshold
}

function onDragEnd() {
  isDragging.value = false
}
</script>

<template>
  <div :class="{ 'table-stats--loading': loading && page === 1 }" class="table table-stats">
    <div class="table-stats__header">
      <template v-if="responseParams">
        <div class="table-stats__header-mode">
          {{ StatsModeLabel[responseParams.mode] }}
        </div>
        <VueDraggableNext
          v-model="responseParams.metrics"
          :remove-clone-on-hide="true"
          :animation="200"
          :class="{
            'table-stats__header-metrics--dragging': isDragging,
          }"
          class="table-stats__header-metrics"
          direction="horizontal"
          @start="onDragStart"
          @end="onDragEnd"
          @drag="onDragging"
        >
          <div
            v-for="(metric, index) in responseParams.metrics"
            :key="index"
            :class="{
              'table-stats__header-metric--will-be-deleted': isOverDeleteThreshold && draggedIndex === index,
            }"
            :style="getWidth(metric)"
            class="table-stats__header-metric"
          >
            <span>
              {{ metric.label }}
            </span>
          </div>
        </VueDraggableNext>
      </template>
      <div class="table-stats__header-apply">
        <v-btn :icon="mdiTune" :size="48" @click="statsDialog?.open()" />
      </div>
    </div>
    <template v-if="responseParams">
      <div v-for="{ date, values } in items" :key="date" class="table-stats__body">
        <div class="table-stats__date">
          {{ formatDateMode(date, responseParams.mode) }}
        </div>
        <div
          v-for="(value, index) in values"
          :key="index"
          :class="`text-${responseParams.metrics[index].color}`"
          :style="getWidth(responseParams.metrics[index])"
        >
          {{ value ? formatPrice(value) : '' }}
        </div>
      </div>
    </template>
  </div>
  <StatsDialog ref="statsDialog" @save="onSave" />
</template>

<style lang="scss" scoped>
.table-stats {
  $padding: 0 20px;
  transition: opacity ease-in-out 0.2s;
  &--loading {
    opacity: 0.3;
  }
  & > div {
    gap: 0 !important;
    padding: 0 !important;
    & > div {
      &:first-child {
        width: 150px;
      }
    }
  }
  &__header {
    position: sticky;
    top: 0;
    text-transform: lowercase;
    background: white;
    z-index: 1;
    &-mode {
      height: 57px;
      border-right: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
      padding: $padding;
      display: inline-flex;
      align-items: center;
    }
    &-metric {
      position: relative;
      line-height: 20px;
      padding: $padding;
      &--will-be-deleted {
        background: rgb(var(--v-theme-error)) !important;
        color: white;
      }
    }
    &-metric {
      align-self: stretch;
      display: flex;
      align-items: center;
      cursor: pointer;
      user-select: none;
    }
    &-metrics {
      display: flex;
      align-self: stretch;
      &:not(.table-stats__header-metrics--dragging) {
        & > div:hover {
          background: rgb(var(--v-theme-bg));
          .v-icon {
            opacity: 1;
          }
        }
      }
    }
    &-apply {
      padding: $padding;
      text-align: right;
    }
  }
  &__body {
    & > div {
      padding: $padding;
    }
  }
  &__date {
    color: rgb(var(--v-theme-gray));
    border-right: thin solid rgba(var(--v-border-color), var(--v-border-opacity));
    height: 57px;
    display: inline-flex;
    width: 100%;
    align-items: center;
  }
}
.sortable-drag {
  .v-icon {
    display: none;
  }
}
.sortable-ghost {
  background: rgba(var(--v-theme-secondary), 0.1);
}
</style>
