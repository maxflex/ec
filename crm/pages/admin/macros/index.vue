<script setup lang="ts">
const macros = ref<MacroResource[]>()

async function loadData() {
  const { data } = await useHttp<ApiResponse<MacroResource>>(`macros`)
  if (data.value) {
    const { data: newItems } = data.value
    macros.value = newItems
  }
}

nextTick(loadData)
</script>

<template>
  <div>
    <Table hoverable>
      <NuxtLink
        v-for="m in macros"
        :key="m.id"
        class="table--item"
        :to="{ name: 'macros-id', params: { id: m.id } }"
      >
        <div>
          {{ m.title }}
        </div>
      </NuxtLink>
    </Table>
  </div>
</template>
