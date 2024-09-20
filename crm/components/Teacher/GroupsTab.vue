<script setup lang="ts">
const { teacherId } = defineProps<{ teacherId: number }>()
const tabName = 'TeacherGroupsTab'
const filters = ref<YearFilters>(loadFilters({
  year: currentAcademicYear(),
}, tabName))
const { items, indexPageData } = useIndex<GroupListResource, YearFilters>(
    `groups`,
    filters,
    {
      tabName,
      staticFilters: {
        teacher_id: teacherId,
      },
    },
)
</script>

<template>
  <UiIndexPage :data="indexPageData">
    <template #filters>
      <v-select
        v-model="filters.year"
        :items="selectItems(YearLabel)"
        label="Учебный год"
        density="comfortable"
      />
    </template>
    <div class="table table--padding">
      <GroupList :items="items" />
    </div>
  </UiIndexPage>
</template>

<style scoped lang="scss">

</style>
