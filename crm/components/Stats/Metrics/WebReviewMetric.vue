<script lang="ts">
interface Filters {
}

const filterDefaults: Filters = {
}

export default {
  label: 'Отзывы',
  filters: { ...filterDefaults },
}
</script>

<script lang="ts" setup>
const filters = ref<Filters>({ ...filterDefaults })
defineExpose({ filters })
</script>

<template>
  <div></div>
</template>
