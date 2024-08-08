<script lang="ts" setup>
export interface Filters {
  year: Year
  program?: Program
  status?: ClientTestStatus
}

const emit = defineEmits<{
  apply: [f: Filters]
}>()

const filters = ref<Filters>({
  year: currentAcademicYear(),
})

watch(filters.value, () => emit('apply', filters.value))
</script>

<template>
  <div class="filters-inputs">
    <div>
      <v-select
        v-model="filters.year"
        :items="selectItems(YearLabel)"
        label="Учебный год"
        density="comfortable"
      />
    </div>
    <!-- <div>
      <UiClearableSelect
        v-model="filters.program"
        :items="selectItems(ProgramLabel)" label="Программа"
        density="comfortable"
      />
    </div> -->
    <div>
      <UiClearableSelect
        v-model="filters.status"
        :items="selectItems(ClientTestStatusLabel)"
        label="Статус"
        density="comfortable"
      />
    </div>
  </div>
</template>
