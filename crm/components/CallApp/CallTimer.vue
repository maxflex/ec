<script setup lang="ts">
import { differenceInSeconds, format, parse } from 'date-fns'

// Define props with types using defineProps
const props = defineProps<{
  item: CallEvent
}>()

// Reactive variable to track the elapsed seconds
const seconds = ref<number>(0)
let interval: ReturnType<typeof setInterval> | null = null

// Computed property to format the elapsed seconds
const secondsFormatted = computed<string>(() => {
  return format(seconds.value * 1000, 'mm:ss')
})

// Lifecycle hook: onMounted to set up the timer
onMounted(() => {
  if (props.item.answered_at) {
    // Parse the answered_at string into a Date object using date-fns
    const answeredAt = parse(props.item.answered_at, 'yyyy-MM-dd HH:mm:ss', new Date())
    // Calculate the difference in seconds from the current time
    seconds.value = differenceInSeconds(new Date(), answeredAt)
    // Set up an interval to increment the seconds counter every second
    interval = setInterval(() => seconds.value++, 1000)
  }
})

// Lifecycle hook: onUnmounted to clear the interval
onUnmounted(() => {
  if (interval) {
    clearInterval(interval)
  }
})
</script>

<template>
  <div v-if="seconds" class="call-timer">
    {{ secondsFormatted }}
  </div>
</template>

<style scoped>
.call-timer {
  display: inline-block;
  width: 38px;
  text-align: left;
}
</style>
