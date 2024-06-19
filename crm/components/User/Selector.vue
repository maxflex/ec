<script setup lang="ts">
const { label } = defineProps<{ label: string }>()
const model = defineModel<number | null>()
const users = ref<UserResource[]>([])
const loading = ref(true)

async function loadData() {
  const { data } = await useHttp<ApiResponse<UserResource[]>>('users')
  if (data.value) {
    users.value = data.value.data
  }
  loading.value = false
}

onMounted(() => loadData())
</script>

<template>
  <v-select
    v-model="model"
    :label="label"
    :items="users"
    :item-title="(e) => formatFullName(e)"
    :item-value="(e) => e.id"
    :loading="loading"
  />
</template>
