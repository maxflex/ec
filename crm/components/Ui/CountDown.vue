<script setup lang="ts">
const { seconds, hours } = defineProps<{
  seconds: number
  hours?: boolean
}>()
const emit = defineEmits(['timeout'])
const { $dayjs } = useNuxtApp()
const countdown = ref(seconds || 0)
const format = hours ? 'HH:mm:ss' : 'mm:ss'
let interval: NodeJS.Timeout

onMounted(() => {
  if (countdown.value > 0) {
    interval = setInterval(() => {
      countdown.value--
      if (countdown.value <= 0) {
        clearInterval(interval)
        emit('timeout')
      }
    }, 1000)
  }
})
</script>

<template>
  <span class="ui-countdown">
    <slot />
    {{ $dayjs.duration(countdown, "second").format(format) }}
  </span>
</template>

<style lang="scss">
.ui-countdown {
  display: flex;
  align-items: center;
  gap: 4px;
}
</style>
