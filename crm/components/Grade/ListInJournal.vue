<script setup lang="ts">
const { items } = defineProps<{
  items: GradeResource[]
}>()
</script>

<template>
  <v-table class="grade-list-for-clients">
    <tbody>
      <tr v-for="item in items" :key="item.id">
        <td width="120">
          {{ formatDate(item.created_at) }}
        </td>
        <td width="150">
          {{ ProgramShortLabel[item.program] }}
        </td>
        <td width="180">
          <UiPerson v-if="item.teacher" :item="item.teacher" />
        </td>
        <td>
          <span :class="`score score--${item.grade}`" class="mr-2">
            {{ item.grade }}
          </span>
        </td>
      </tr>
    </tbody>
  </v-table>
</template>

<style lang="scss">
.grade-list-for-clients {
  border-top: 1px solid rgb(var(--v-theme-border));
}
</style>
