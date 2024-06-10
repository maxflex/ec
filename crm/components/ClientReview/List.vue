<script setup lang="ts">
const { items } = defineProps<{ items: [] }>()
</script>

<template>
  <div class="table">
    <div
      v-for="item in items"
      :key="item.id"
    >
      <div style="width: 50px">
        {{ item.id }}
      </div>
      <div style="width: 200px">
        <NuxtLink :to="{ name: 'clients-id', params: { id: item.client_id } }">
          {{ formatName(item.client) }}
        </NuxtLink>
      </div>
      <div style="width: 350px">
        <NuxtLink :to="{ name: 'teachers-id', params: { id: item.teacher_id } }">
          {{ formatFullName(item.teacher) }}
        </NuxtLink>
      </div>
      <div style="width: 250px">
        {{ ProgramLabel[item.program] }}
      </div>

      <!-- <div style="width: 350px">
        <NuxtLink :to="{ name: 'teachers-id', params: { id: item.id } }">
          {{ formatFullName(item) }}
        </NuxtLink>
      </div>
      <div style="width: 250px">
        {{ TeacherStatusLabel[item.status] }}
      </div>
      <div>
        {{ item.subjects.map(s => SubjectLabelShort[s]).join('+') }}
      </div> -->
      <div
        class="text-gray"
        style="flex: 1"
      >
        {{ formatDateTime(item.created_at) }}
      </div>
      <div
        class="text-right"
        style="width: 100px; flex: initial !important"
      >
        <UiRating v-model="item.rating" />
      </div>
    </div>
  </div>
</template>
