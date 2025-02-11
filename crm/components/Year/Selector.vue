<script setup lang="ts">
const { clientId, mode } = defineProps<{
  clientId?: number
  mode: 'reports' | 'schedule'
}>()

const model = defineModel<Year>()
const loaded = ref(false)
const availableYears = ref<Year[]>([])

async function loadYears() {
  const { data } = await useHttp<Year[]>(
    `common/years`,
    {
      params: {
        mode,
        client_id: clientId,
      },
    },
  )
  availableYears.value = data.value!
  loaded.value = true
}

nextTick(loadYears)
</script>

<template>
  <v-select
    v-model="model"
    :loading="!loaded"
    :items="availableYears.map(year => ({
      value: year,
      text: YearLabel[year],
    }))"
    label="Год"
  />
</template>
