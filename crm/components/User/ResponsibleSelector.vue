<script setup lang="ts">
const model = defineModel<number | undefined>()
const users = ref<UserResource[]>()

async function loadData() {
  const { data } = await useHttp<ApiResponse<UserResource>>(`users`, {
    params: {
      has_responsible_requests: true,
    },
  })
  users.value = data.value!.data
}

nextTick(loadData)
</script>

<template>
  <UiClearableSelect
    v-model="model"
    v-bind="$attrs"
    :items="users"
    :item-title="formatName"
    item-value="id"
    :loading="users === undefined"
    label="Ответственный"
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
