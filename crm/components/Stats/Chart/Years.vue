<script setup lang="ts">
import type { ChartData, ChartDataset, ChartOptions } from 'chart.js'
import type { StatsListResource, StatsParams } from '..'
import { Chart, registerables } from 'chart.js'
import { format, getMonth, parseISO } from 'date-fns'
import { BarChart } from 'vue-chart-3'
import { colors } from '~/plugins/vuetify'

const { items, params } = defineProps<{
  items: StatsListResource[] // [{ date: '2024-01-01', values: number[] }, ...]
  params: StatsParams // { metrics: [{ label, color, ... }], ... }
}>()

const YEAR_COLORS = [
  colors.secondary,
  colors.success,
  colors.orange,
  colors.error,
  colors.accent,
  '#8b5cf6',
  '#ec4899',
  '#14b8a6',
  '#f97316',
  '#0ea5e9',
]

Chart.register(...registerables)

const chartData: ChartData<'bar'> = {
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
const yearColors: Record<number, string> = {}
years.slice().reverse().forEach((y, i) => {
  yearColors[y] = YEAR_COLORS[i % YEAR_COLORS.length]
})

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
chartData.datasets = params.metrics.flatMap((metric, mi): ChartDataset<'bar'>[] => {
  return years.map((year) => {
    const color = yearColors[year]
    const data = labelsMMDD.map((mmdd) => {
      const v = valuesByYear[year]?.[mmdd]?.[mi]
      return typeof v === 'number' ? v : Number.NaN
    })

    return {
      label: `${metric.label} • ${year}`,
      data,
      borderColor: color,
      backgroundColor: color,
      year, // <— добавили
    }
  })
})

const chartOptions: ChartOptions<'bar'> = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: true,
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
        title: (ctx) => {
          const c = ctx[0]
          const year = (c.dataset as any).year as number
          const mmdd = labelsMMDD[c.dataIndex] // 'MM-dd'
          const iso = `${year}-${mmdd}` // 'YYYY-MM-dd'

          const month = getMonth(iso)
          const monthLabel = MonthLabelShort[month + 1 as Month]
          return monthLabel
        },
        label: ctx => `${ctx.dataset.label}: ${formatPrice(ctx.parsed.y, true)}`,
      },
    },
  },
  scales: {
    x: {
      beginAtZero: true,
      grid: {
        display: false,
      },
      ticks: {
        autoSkip: true,
        font: {
          size: 14,
        },
        // maxTicksLimit: 12,
        // maxRotation: 0
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
</script>

<template>
  <BarChart :chart-data="chartData" :options="chartOptions" />
</template>
