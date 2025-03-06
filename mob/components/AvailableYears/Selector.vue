<script setup lang="ts">
import type { AvailableYearsParams } from '.'

const { clientId, teacherId, mode } = defineProps<AvailableYearsParams>()
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
  <UiChipSelector
    v-model="model"
    :items="availableYears.map(year => ({
      value: year,
      title: YearLabel[year],
    }))"
    :label="YearLabel[currentAcademicYear()]"
    :disabled="loading || availableYears.length <= 1"
  />
</template>
