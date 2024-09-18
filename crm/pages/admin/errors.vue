<script setup lang="ts">
const filters = ref<{
  code?: ErrorCode
}>({})

const loading = ref(false)

const { items, reloadData, indexPageData } = useIndex<ErrorResource>(`errors`, filters)

async function check() {
  loading.value = true
  await useHttp(`errors/check`, { method: 'post' })
  loading.value = false
  reloadData()
}
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <UiClearableSelect
        v-model="filters.code"
        :items="selectItems(ErrorCodeLabel)"
        label="Код ошибки"
        density="comfortable"
      />
    </template>
    <template #buttons>
      <v-btn color="primary" :loading="loading" @click="check()">
        обновить ошибки
        <template #append>
          <v-icon icon="$loading" />
        </template>
      </v-btn>
    </template>
    <ErrorList :items="items" />
  </UiIndexPage>
</template>
