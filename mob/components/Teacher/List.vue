<script setup lang="ts">
const { items } = defineProps<{
  items: TeacherListResource[]
}>()
</script>

<template>
  <div class="table">
    <div
      v-for="teacher in items"
      :key="teacher.id"
    >
      <div style="width: 50px">
        {{ teacher.id }}
      </div>
      <div style="width: 350px">
        <NuxtLink :to="{ name: 'teachers-id', params: { id: teacher.id } }">
          {{ formatFullName(teacher) }}
        </NuxtLink>
      </div>
      <div style="width: 250px">
        {{ TeacherStatusLabel[teacher.status] }}
      </div>
      <div style="width: 200px">
        <span v-if="teacher.is_published">
          опубликован
        </span>
        <span v-else class="text-gray">
          не опубликован
        </span>
      </div>
      <div>
        {{ teacher.subjects.map(s => SubjectLabelShort[s]).join('+') }}
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">

</style>
