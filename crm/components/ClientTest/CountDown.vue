<script setup lang="ts">
import type { ClientTestResource } from '.'

const { item } = defineProps<{ item: ClientTestResource }>()
const emit = defineEmits<{
  timeout: [item: ClientTestResource]
}>()
const seconds = ref(item.seconds_left || 0)
const { $dayjs } = useNuxtApp()
let interval: NodeJS.Timeout

onMounted(() => {
  if (seconds.value > 0) {
    interval = setInterval(() => {
      seconds.value--
      if (seconds.value <= 0) {
        clearInterval(interval)
        setTimeout(() => emit('timeout', item), 1000)
      }
    }, 1000)
  }
})

defineExpose({ seconds })
</script>

<template>
  <span v-if="item.is_active" class="font-weight-bold">
    {{ $dayjs.duration(seconds, "second").format("mm:ss") }}
  </span>
</template>
