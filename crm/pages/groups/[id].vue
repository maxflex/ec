<script setup lang="ts">
import type { Group } from "~/utils/models"

const route = useRoute()
const group = ref<Group>()

async function loadData() {
  const { data } = await useHttp(`groups/${route.params.id}`)
  group.value = data.value as Group
}

onMounted(async () => {
  await loadData()
})
</script>

<template>
  <h3 class="page-title">Группа {{ route.params.id }}</h3>
  <pre>
  <code class="group-id" v-if="group">
    {{ group }}
  </code>
  </pre>
</template>

<style lang="scss">
.groups-id {
  padding: 20px;
}
</style>
