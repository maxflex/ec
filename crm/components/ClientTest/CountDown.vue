<script setup lang="ts">
import type { ClientTestResource } from '.'
import { addSeconds, format } from 'date-fns'

const { item } = defineProps<{ item: ClientTestResource }>()
const emit = defineEmits<{
  timeout: [item: ClientTestResource]
}>()
const seconds = ref(item.seconds_left || 0)
let interval: NodeJS.Timeout

const timeLeft = computed(() => {
  const dummyDate = new Date(0) // Epoch time
  return format(addSeconds(dummyDate, seconds.value), 'mm:ss')
})

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
</script>

<template>
  <span v-if="item.is_active" class="font-weight-bold">
    {{ timeLeft }}
  </span>
</template>
