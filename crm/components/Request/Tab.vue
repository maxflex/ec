<script setup lang="ts">
const { clientId } = defineProps<{ clientId: number }>()
const loading = ref(true)
const items = ref<RequestListResource[]>([])

async function loadData() {
  loading.value = true
  const { data } = await useHttp<ApiResponse<RequestListResource[]>>(
      `requests`,
      {
        params: {
          client_id: clientId,
        },
      },
  )
  if (data.value) {
    items.value = data.value.data
  }
  loading.value = false
}

const noData = computed(() => !loading.value && items.value.length === 0)

nextTick(loadData)
</script>

<template>
  <UiIndexPage :data="{ loading, noData }">
    <RequestList :model-value="items" />
  </UiIndexPage>
</template>
