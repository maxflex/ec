<script setup lang="ts">
import { differenceInSeconds, format, parse } from 'date-fns'

const { item } = defineProps<{
  item: {
    finished_at: string
    answered_at: string | null
  }
}>()

const dateFormat = 'yyyy-MM-dd HH:mm:ss'
const answeredAt = parse(item.answered_at!, dateFormat, new Date())
const finishedAt = parse(item.finished_at, dateFormat, new Date())
const seconds = differenceInSeconds(finishedAt, answeredAt)
const callDuration = format(new Date(seconds * 1000), 'mm:ss')
</script>

<template>
  <span>
    {{ callDuration }}
  </span>
</template>
