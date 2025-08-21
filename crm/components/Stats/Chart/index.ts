import type { ChartOptions } from 'chart.js'
import type { StatsListResource, StatsParams } from '..'
import { formatDateMode } from '..'

export function getChartOptions(items: StatsListResource[], params: StatsParams): ChartOptions {
  return {
    plugins: {
      legend: {
        // display: false,
        labels: {
          usePointStyle: true, // this makes the marker shape match `pointStyle`
          pointStyle: 'circle', // circle instead of square
        },
      },
      tooltip: {
        callbacks: {
          title: (context) => {
            const dateIndex = context[0].dataIndex
            const date = items[dateIndex].date
            return formatDateMode(date, params.mode, params.date_to)
          },
        },
      },
    },
    scales: {
      x: {
        ticks: {
          autoSkip: true,
          maxTicksLimit: 30,
        },
      },
    },
  }
}
