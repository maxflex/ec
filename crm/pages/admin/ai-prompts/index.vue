<script setup lang="ts">
const prompts = ref<AiPromptResource[]>()

async function loadData() {
  const { data } = await useHttp<ApiResponse<AiPromptResource>>('ai-prompts')
  if (data.value) {
    const { data: newItems } = data.value
    prompts.value = newItems
  }
}

nextTick(loadData)
</script>

<template>
  <div>
    <div class="table table--hover">
      <NuxtLink
        v-for="p in prompts"
        :key="p.id"
        class="table--item"
        :to="{ name: 'ai-prompts-id', params: { id: p.id } }"
      >
        <div>
          {{ p.title }}
        </div>
      </NuxtLink>
    </div>
  </div>
</template>
