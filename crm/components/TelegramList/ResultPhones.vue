<script setup lang="ts">
const { items } = defineProps<{
  items: TelegramListResult[]
}>()
</script>

<template>
  <div
    v-for="r in item"
    :key="r.id"
    :class="{
      'opacity-5': item.status !== 'sent',
      'text-success': r.is_sent,
      'text-error': !r.is_sent,
    }"
  >
    {{ formatPhone(r.number) }}
  </div>
</template>

<style scoped lang="scss">

</style>
