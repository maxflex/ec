<script setup lang="ts">
const { apiUrl, filters } = defineProps<{
  apiUrl: string
  filters?: object
}>()
const items = ref<[]>([])

onMounted(async () => {
  const { data } = await useHttp<ApiResponse<[]>>(apiUrl, {
    params: filters,
  })
  if (data.value) {
    items.value = data.value.data
    console.log('DATA', data.value.data)
  }
})
</script>

<template>
  <slot :items="items" />
</template>
