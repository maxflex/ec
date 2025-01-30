<script setup lang="ts">
interface Filters {
  program?: Program
}

const filters = ref<Filters>({})
const availablePrograms = ref<Program[]>([])

const { items, indexPageData } = useIndex<ClientReviewListResource, Filters>(
  `client-reviews`,
  filters,
)

async function loadAvailablePrograms() {
  const { data } = useHttp(`client-reviews`, {
    params: {
      unique: 'program',
    },
  })
  console.log(data.value)
}

nextTick(loadAvailablePrograms)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <UiClearableSelect
        v-model="filters.program"
        label="Программа"
        :items="availablePrograms"
        density="comfortable"
      />
    </template>
    <ClientReviewList :items="items" />
  </UiIndexPage>
</template>
