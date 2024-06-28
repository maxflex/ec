<script setup lang="ts">
const props = withDefaults(defineProps<{
  url: string
  filters: object
}>(), {
  filters: () => ({}),
})
const { url, filters } = toRefs(props)
const loading = ref(true)
const items = ref<[]>([])

async function loadData() {
  console.log('LOAD DATA', filters)
  loading.value = true
  const { data } = await useHttp<ApiResponse<[]>>(url.value, {
    params: filters.value,
  })
  if (data.value) {
    items.value = data.value.data
    // console.log('DATA', data.value.data)
  }
  loading.value = false
}

onMounted(loadData)

watch(
  filters,
  (newFilters) => {
    console.log('Filters updated:', newFilters)
    loadData()
  },
  { deep: true },
)
</script>

<template>
  <slot name="filters" />
  <UiLoaderr3 v-if="loading" />
  <slot
    v-else-if="items.length > 0"
    :items="items"
  />
  <UiNoData v-else />
</template>
