<script setup lang="ts">
const { clientId, teacherId, mode } = defineProps<{
  clientId?: number
  teacherId?: number
  mode: 'reports' | 'schedule' | 'grades'
}>()
const emit = defineEmits(['loaded'])
const model = defineModel<Year>()
const loading = ref(true)
const availableYears = ref<Year[]>([])

async function loadAvailableYears() {
  const { data } = await useHttp<ApiResponse<Year>>(
    `common/available-years`,
    {
      params: {
        mode,
        client_id: clientId,
        teacher_id: teacherId,
      },
    },
  )
  availableYears.value = data.value!.data
  // если что-то есть, выбираем первый
  if (availableYears.value.length) {
    model.value = availableYears.value[0]
  }
  loading.value = false
  emit('loaded')
}

nextTick(loadAvailableYears)
</script>

<template>
  <v-select
    v-model="model"
    :loading="loading"
    :items="availableYears.map(year => ({
      value: year,
      title: YearLabel[year],
    }))"
    label="Год"
    density="comfortable"
    :disabled="!loading && availableYears.length <= 1"
  />
  <!-- <v-select
      v-model="selectedYear"
      :disabled="availableYears.length <= 1"
      :loading="!yearsLoaded"
      label="Учебный год"
      :items="
        availableYears.map((value) => ({
          value,
          title: YearLabel[value],
        }))
      "
      density="comfortable"
    /> -->
</template>
