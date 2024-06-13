<script setup lang="ts">
const { label } = withDefaults(
  defineProps<{ label: string }>(),
  {
    label: 'Преподаватель',
  },
)

const model = defineModel<number | undefined>()
const teachers = ref<TeacherListResource[]>([])
const loading = ref(true)

async function loadData() {
  const { data } = await useHttp<ApiResponse<TeacherListResource[]>>('teachers')
  if (data.value) {
    teachers.value = data.value.data
  }
  loading.value = false
}

onMounted(() => loadData())
</script>

<template>
  <UiClearableSelect
    v-bind="$attrs"
    v-model="model"
    :label="label"
    :loading="loading"
    :items="teachers.map(t => ({
      value: t.id,
      title: formatFullName(t),
    }))"
  />
</template>
