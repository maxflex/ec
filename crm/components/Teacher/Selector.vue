<script setup lang="ts">
const { label = 'Преподаватель', headTeachers } = defineProps<{
  label?: string
  // отображать кураторов (тех, у кого есть "русский" в предметах)
  headTeachers?: boolean
}>()
const model = defineModel<number | null >()
const teachers = useTeachers()

function isActive(teacher: TeacherListResource) {
  if (headTeachers) {
    return teacher.subjects.includes('rus') && teacher.status === 'active'
  }
  return teacher.status === 'active'
}

const items = computed(() => {
  if (!Array.isArray(teachers.value)) {
    return []
  }
  if (headTeachers) {
    return [...teachers.value].sort((a, b) => {
      const aPriority = isActive(a) ? 1 : 0
      const bPriority = isActive(b) ? 1 : 0
      return bPriority - aPriority
    })
  }
  return teachers.value
})
</script>

<template>
  <UiClearableSelect
    v-model="model"
    v-bind="$attrs"
    :items="items"
    :item-title="formatFullName"
    item-value="id"
    :loading="teachers === undefined"
    :label="label"
  >
    <template #item="{ props, item }">
      <v-list-item
        v-bind="props"
        :class="{ 'text-gray': !isActive(item.raw) }"
      >
        <template #prepend />
        <template #title>
          {{ item.title }}
        </template>
      </v-list-item>
    </template>
  </UiClearableSelect>
</template>
