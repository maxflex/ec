<script setup lang="ts">
const { url, filters } = defineProps<{
  url: string
  filters?: object
}>()
const loading = ref(true)
const items = ref<[]>([])

onMounted(async () => {
  const { data } = await useHttp<ApiResponse<[]>>(url, {
    params: filters,
  })
  if (data.value) {
    items.value = data.value.data
    // console.log('DATA', data.value.data)
  }
  loading.value = false
})
</script>

<template>
  <UiLoaderr v-if="loading" />
  <slot
    v-else-if="items.length > 0"
    :items="items"
  />
  <UiNoData v-else />
</template>
