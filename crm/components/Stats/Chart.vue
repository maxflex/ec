<script setup lang="ts">
import type { ChartData, ChartOptions } from 'chart.js'
import type { StatsListResource, StatsParams } from '.'
import { Chart, registerables } from 'chart.js'
import { BarChart, LineChart } from 'vue-chart-3'
import { MetricColorHex } from './Metrics'

const { items, params } = defineProps<{
  items: StatsListResource[]
  params: StatsParams
}>()

Chart.register(...registerables)

const ChartComponent = params.display === 'line' ? LineChart : BarChart

const chartData: ChartData<'line' | 'bar'> = {
  labels: [],
  datasets: [],
}

const chartOptions: ChartOptions = {
  plugins: {
    legend: {
      // display: false,
      labels: {
        usePointStyle: true, // this makes the marker shape match `pointStyle`
        pointStyle: 'circle', // circle instead of square
      },
    },
  },
  scales: {
    x: {
      ticks: {
        autoSkip: true,
        maxTicksLimit: 30, // show at most 10 ticks
      },
    },
  },
}

// chartData.labels = params.metrics.map(e => e.label)
chartData.labels = items.map(e => formatDate(e.date))

chartData.datasets = params.metrics.map((metric, i) => ({
  label: metric.label,
  backgroundColor: MetricColorHex[metric.color],
  borderColor: MetricColorHex[metric.color],
  tension: 0.4, // This makes the lines smooth/curved
  pointRadius: 3,
  data: items.map(e => e.values[i]),
}))
</script>

<template>
  <component
    :is="ChartComponent"
    :chart-data="(chartData as ChartData<'line'>)"
    :options="chartOptions"
  />
</template>
