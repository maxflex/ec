<script setup lang="ts">
import type { ChartData, ChartOptions } from 'chart.js'
import type { StatsListResource, StatsParams } from '..'
import { Chart, registerables } from 'chart.js'
import { BarChart } from 'vue-chart-3'
import { getChartOptions } from '.'
import { MetricColorHex } from '../Metrics'

const { items, params } = defineProps<{
  items: StatsListResource[]
  params: StatsParams
}>()

Chart.register(...registerables)

const chartOptions: ChartOptions = getChartOptions(items, params)

const chartData: ChartData<'bar'> = {
  labels: [],
  datasets: [],
}

chartData.labels = items.map(e => formatDate(e.date))

chartData.datasets = params.metrics.map((metric, i) => ({
  label: metric.label,
  backgroundColor: MetricColorHex[metric.color],
  borderColor: MetricColorHex[metric.color],
  tension: 0.5, // This makes the lines smooth/curved
  pointRadius: 2, // hide points
  pointHoverRadius: 5,
  borderWidth: 2, // slightly thicker line
  data: items.map(e => e.values[i]),
}))
</script>

<template>
  <BarChart :chart-data="chartData" :options="chartOptions" />
</template>
