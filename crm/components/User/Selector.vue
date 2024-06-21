<script setup lang="ts">
const { label } = defineProps<{ label: string }>()
const model = defineModel<number | null>()
const users = ref<UserResource[]>([])
const loading = ref(true)

async function loadData() {
  const { data } = await useHttp<ApiResponse<UserResource[]>>('users')
  if (data.value) {
    users.value = data.value.data.sort((a, b) => Number(b.is_active) - Number(a.is_active))
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
    :item-title="(e) => formatName(e)"
    :item-value="(e) => e.id"
    :loading="loading"
  />
</template>
