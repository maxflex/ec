<script setup lang="ts">
const model = defineModel<number | null>()
const users = ref<UserResource[]>([])
const loading = ref(true)

async function loadData() {
  const { data } = await useHttp<ApiResponse<UserResource>>(`users`)
  if (data.value) {
    users.value = data.value.data.sort((a, b) => Number(b.is_active) - Number(a.is_active))
  }
  loading.value = false
}

onMounted(() => loadData())
</script>

<template>
  <UiClearableSelect
    v-model="model"
    v-bind="$attrs"
    :loading="loading"
    :items="users"
    :item-title="formatName"
    item-value="id"
  >
    <template #item="{ props, item }">
      <v-list-item v-bind="props" :class="{ 'text-gray': !item.raw.is_active }">
        <template #prepend />
        <template #title>
          {{ item.title }}
        </template>
      </v-list-item>
    </template>
  </UiClearableSelect>
</template>
