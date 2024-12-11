<script setup lang="ts">
const props = defineProps<{ seconds: number }>()
const emit = defineEmits(['timeout'])
const seconds = ref(props.seconds || 0)
const { $dayjs } = useNuxtApp()
let interval: NodeJS.Timeout

onMounted(() => {
  if (seconds.value > 0) {
    interval = setInterval(() => {
      seconds.value--
      if (seconds.value <= 0) {
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
    {{ $dayjs.duration(seconds, "second").format("mm:ss") }}
  </span>
</template>

<style lang="scss">
.ui-countdown {
  display: flex;
  align-items: center;
  gap: 4px;
}
</style>
