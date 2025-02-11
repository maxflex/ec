<script setup lang="ts">
const filters = ref<AvailableYearsFilter>({ })
const { user } = useAuthStore()
const selectedProgram = ref<Program>()

const { items, indexPageData } = useIndex<GroupListResource, AvailableYearsFilter>(
  `groups`,
  filters,
  {
    instantLoad: false,
  },
)

const availablePrograms = computed(() => {
  return [...new Set(items.value.map(e => e.program))].map(p => ({
    value: p,
    title: ProgramLabel[p],
  }))
})

const filteredItems = computed(() => selectedProgram.value
  ? items.value.filter(e => e.program === selectedProgram.value)
  : items.value,
)

watch(filters.value, () => {
  selectedProgram.value = undefined
})
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <AvailableYearsSelector
        v-model="filters.year"
        :teacher-id="user!.id"
        mode="groups"
      />
      <UiClearableSelect
        v-model="selectedProgram"
        label="Программа"
        :items="availablePrograms"
        density="comfortable"
      />
    </template>
    <GroupTeacherList :items="filteredItems" blur-others />
  </UiIndexPage>
</template>
