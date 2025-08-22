<script setup lang="ts">
import type { ChartData, ChartOptions } from 'chart.js'
import type { StatsListResource, StatsParams } from '..'
import { Chart, registerables } from 'chart.js'
import { format } from 'date-fns'
import { BarChart } from 'vue-chart-3'
import { formatDateMode } from '..'
import { MetricColorHex } from '../Metrics'

const { items, params } = defineProps<{
  items: StatsListResource[]
  params: StatsParams
}>()

Chart.register(...registerables)

const chartOptions: ChartOptions = {
  plugins: {
    legend: {
      // отображать только когда несколько метрик
      display: params.metrics.length > 1,
      labels: {
        padding: 20,
        usePointStyle: true, // this makes the marker shape match `pointStyle`
        pointStyle: 'circle', // circle instead of square
      },
    },
    tooltip: {
      mode: 'index',
      usePointStyle: true,
      titleMarginBottom: 10,
      padding: 10,
      boxPadding: 10,
      titleFont: {
        size: 14,
      },
      bodyFont: {
        size: 14,
      },
      bodySpacing: 10,
      callbacks: {
        title: (context) => {
          const dateIndex = context[0].dataIndex
          const date = items[dateIndex].date
          return formatDateMode(date, params.mode, params.date_to)
        },
        label: ctx => `${ctx.dataset.label}: ${formatPrice(ctx.parsed.y, true)}`,
      },
    },
  },
  scales: {
    x: {
      beginAtZero: true,
      ticks: {
        autoSkip: true,
        maxTicksLimit: 30,
        font: {
          size: 14,
        },
      },
    },
    y: {
      beginAtZero: true,
      grid: {
        color: ctx => (Number(ctx.tick.value) === 0 ? 'red' : 'rgba(0,0,0,0.06)'),
        lineWidth: ctx => (Number(ctx.tick.value) === 0 ? 2 : 1),
      },
      ticks: {
        precision: 0,
        callback: value => formatPrice(Number(value), true),
        font: {
          size: 14,
        },
      },

    },
  },

}

const chartData: ChartData<'bar'> = {
  labels: [],
  datasets: [],
}

function fmtDate(dateTime: string | null): string {
  if (!dateTime) {
    return ''
  }
  return format(dateTime, 'MM.yy')
}

chartData.labels = items.map(e => fmtDate(e.date))

chartData.datasets = params.metrics.map((metric, i) => ({
  label: metric.label,
  backgroundColor: MetricColorHex[metric.color],
  borderColor: MetricColorHex[metric.color],
  data: items.map(e => e.values[i]),
}))
</script>

<template>
  <BarChart :chart-data="chartData" :options="chartOptions" />
</template>
