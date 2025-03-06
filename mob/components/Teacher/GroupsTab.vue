<script setup lang="ts">
const { teacherId } = defineProps<{ teacherId: number }>()
const filters = ref<AvailableYearsFilter>({ })
const availableYearsLoaded = ref(false)
const { items, indexPageData } = useIndex<GroupListResource, AvailableYearsFilter>(
  `groups`,
  filters,
  {
    instantLoad: false,
    staticFilters: {
      teacher_id: teacherId,
    },
  },
)

const noData = computed(() => availableYearsLoaded.value && !filters.value.year)

function onAvailableYearsLoaded() {
  availableYearsLoaded.value = true
}
</script>

<template>
  <UiIndexPage :data="availableYearsLoaded && noData ? { noData, loading: false } : indexPageData">
    <template #filters>
      <AvailableYearsSelector
        v-model="filters.year"
        mode="groups"
        :teacher-id="teacherId"
        @loaded="onAvailableYearsLoaded()"
      />
    </template>
    <GroupList :items="items" />
  </UiIndexPage>
</template>
