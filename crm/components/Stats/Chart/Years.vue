<script setup lang="ts">
import type { ChartData, ChartDataset, ChartOptions } from 'chart.js'
import type { StatsListResource, StatsParams } from '..'
import { Chart, registerables } from 'chart.js'
import { format, parseISO } from 'date-fns'
import { LineChart } from 'vue-chart-3'
import { formatDateMode } from '..'
import { MetricColorHex } from '../Metrics'

const { items, params } = defineProps<{
  items: StatsListResource[] // [{ date: '2024-01-01', values: number[] }, ...]
  params: StatsParams // { metrics: [{ label, color, ... }], ... }
}>()

Chart.register(...registerables)

const chartData: ChartData<'line'> = {
  labels: [],
  datasets: [],
}

// 1) Нормализуем ось X к MM-DD
const mmddSet = new Set<string>()
for (const it of items) mmddSet.add(format(parseISO(it.date), 'MM-dd'))
const labelsMMDD = Array.from(mmddSet).sort()

// 2) Собираем значения по (год -> MM-DD -> values[])
type MapY = Record<number, Record<string, number[]>>
const valuesByYear: MapY = {}

for (const it of items) {
  const d = parseISO(it.date)
  const y = d.getFullYear()
  const key = format(d, 'MM-dd')
  valuesByYear[y] ??= {}
  valuesByYear[y][key] = it.values
}

const years = Object.keys(valuesByYear).map(Number).sort()

// компактные подписи по оси X (без года)
chartData.labels = labelsMMDD.map(k => format(parseISO(`2024-${k}`), 'dd.MM'))

// 3) Кодируем "год" штриховкой, "метрику" — цветом
// const dashByYear = [
//   [],           // сплошная — например, самый свежий год
//   [6, 4],       // длинный штрих
//   [3, 3],       // средний
//   [2, 2],       // частый
//   [10, 3, 2, 3] // пунктир-точка
// ]

// 4) Формируем датасеты: на каждую метрику и на каждый год
chartData.datasets = params.metrics.flatMap((metric, mi): ChartDataset<'line'>[] => {
  const color = MetricColorHex[metric.color] // напр. '#2563EB'
  return years.map((year) => {
    const data = labelsMMDD.map((mmdd) => {
      const v = valuesByYear[year]?.[mmdd]?.[mi]
      return typeof v === 'number' ? v : Number.NaN
    })
    return {
      label: `${metric.label} • ${year}`,
      data,
      borderColor: color,
      backgroundColor: `${color}33`, // прозрачная подложка
      borderWidth: 2,
      tension: 0.5,
      pointRadius: 0,
      fill: false, // при множестве серий лучше без заливки
      spanGaps: true,
      // borderDash: dashByYear[yi % dashByYear.length],
      // чтобы легенда могла скрывать/показывать серии по клику
    }
  })
})

const chartOptions: ChartOptions<'line'> = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: true,
      labels: {
        usePointStyle: true, // this makes the marker shape match `pointStyle`
        pointStyle: 'circle', // circle instead of square
      },
    },
    tooltip: {
      intersect: false,
      mode: 'index',
      callbacks: {
        title: (context) => {
          const dateIndex = context[0].dataIndex
          const date = items[dateIndex].date
          return formatDateMode(date, params.mode, params.date_to)
        },
        label: ctx => `${ctx.dataset.label}: ${ctx.parsed.y}`,
      },
    },
  },
  scales: {
    x: {
      grid: {
        display: false,
      },
      ticks: {
        autoSkip: true,
        // maxTicksLimit: 12,
        // maxRotation: 0
      },
    },
    y: {
      grid: { color: 'rgba(0,0,0,0.06)' },
      ticks: { precision: 0 },
    },
  },
}
</script>

<template>
  <LineChart :chart-data="chartData" :options="chartOptions" />
</template>
