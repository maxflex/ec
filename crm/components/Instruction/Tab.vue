<script setup lang="ts">
const { teacherId } = defineProps<{ teacherId: number }>()
const loading = ref(true)
const items = ref<InstructionTeacherListResource[]>([])

async function loadData() {
  loading.value = true
  const { data } = await useHttp<ApiResponse<InstructionTeacherListResource[]>>(
      `instructions`,
      {
        params: {
          teacher_id: teacherId,
        },
      },
  )
  if (data.value) {
    items.value = data.value.data
  }
  loading.value = false
}

const noData = computed(() => !loading.value && items.value.length === 0)

nextTick(loadData)
</script>

<template>
  <UiIndexPage :data="{ loading, noData }">
    <InstructionTeacherList :items="items" />
  </UiIndexPage>
</template>
