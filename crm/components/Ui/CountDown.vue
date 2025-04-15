<script setup lang="ts">
import { addSeconds, format } from 'date-fns'

const { seconds, hours } = defineProps<{
  seconds: number
  hours?: boolean
}>()

const emit = defineEmits(['timeout'])
const secondsLeft = ref(seconds || 0)
let interval: NodeJS.Timeout
const formatString = hours ? 'HH:mm:ss' : 'mm:ss'

const secondsLeftFormatted = computed(() => {
  const dummyDate = new Date(0) // Epoch time
  return format(addSeconds(dummyDate, secondsLeft.value), formatString)
})

onMounted(() => {
  if (secondsLeft.value > 0) {
    interval = setInterval(() => {
      secondsLeft.value--
      if (secondsLeft.value <= 0) {
        clearInterval(interval)
        setTimeout(() => emit('timeout'), 1000)
      }
    }, 1000)
  }
})
</script>

<template>
  <span class="ui-countdown">
    <slot />
    {{ secondsLeftFormatted }}
  </span>
</template>

<style lang="scss">
.ui-countdown {
  display: flex;
  align-items: center;
  gap: 4px;
}
</style>
