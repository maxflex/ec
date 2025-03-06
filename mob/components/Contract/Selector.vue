<script setup lang="ts">
const loading = ref(true)
const model = defineModel<number>({ required: true })
const items = ref<ContractEditPriceResource[]>([])

// Выбор договора в редактировании цены ClientLesson/EditPriceDialog.vue
async function loadData() {
  loading.value = true
  const { data } = await useHttp<ContractEditPriceResource[]>(
    `contract-version-programs`,
    {
      params: {
        id: model.value,
      },
    },
  )
  items.value = data.value!
  loading.value = false
}

nextTick(loadData)
</script>

<template>
  <v-select
    v-model="model"
    label="Договор"
    :loading="loading"
    :items="items.map(e => ({
      value: e.id,
      title: `№${e.contract.id} ${CompanyLabel[e.contract.company]} на ${YearLabel[e.contract.year]}`,
    }))"
  />
</template>
