<script setup lang="ts">
import type { Users } from "~/utils/models"

const { label } = defineProps<{ label: string }>()
const model = defineModel<number | null>()
const users = ref<Users>([])
const loading = ref(true)

async function loadData() {
  const { data } = await useHttp<ApiResponse<Users>>("users")
  if (data.value) {
    users.value = data.value.data
  }
  loading.value = false
}

onMounted(() => loadData())
</script>

<template>
  <v-select
    :label="label"
    v-model="model"
    :items="users"
    :item-title="(e) => formatFullName(e)"
    :item-value="(e) => e.id"
    :loading="loading"
  >
  </v-select>
</template>
