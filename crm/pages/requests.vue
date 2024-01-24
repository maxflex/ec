<script setup lang="ts">
const items = ref<Requests>()

const loadData = async function () {
  const { data } = await useHttp<ApiResponse<Requests>>("requests")
  items.value = data.value?.data
}

onMounted(async () => {
  // https://github.com/vuejs/core/issues/6638
  // https://github.com/nuxt/nuxt/issues/25131
  await nextTick()
  await loadData()
})
</script>
<template>
  <div class="requests">
    <RequestItem v-for="item in items" :item="item" />
  </div>
  <div class="text-center my-12">
    <v-btn @click="loadData()">показать ещё</v-btn>
  </div>
</template>

<style lang="scss">
.requests {
  background: #fafafa;
}
</style>
