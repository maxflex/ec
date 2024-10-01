<script setup lang="ts">
const { item } = defineProps<{ item: ClientTestResource }>()
const seconds = ref(item.seconds_left || 0)
const { $dayjs } = useNuxtApp()
let interval: NodeJS.Timeout

onMounted(() => {
  if (seconds.value > 0) {
    interval = setInterval(() => {
      seconds.value--
      if (seconds.value <= 0) {
        clearInterval(interval)
      }
    }, 1000)
  }
})

defineExpose({ seconds })
</script>

<template>
  <span v-if="item.is_active" class="client-test-countdown">
    <v-icon icon="$time" class="vfn-1" />
    {{ $dayjs.duration(seconds, "second").format("mm:ss") }}
  </span>
</template>

<style lang="scss">
.client-test-countdown {
  display: flex;
  align-items: center;
  gap: 4px;
}
</style>
