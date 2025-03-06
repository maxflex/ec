<script setup lang="ts">
const { items } = defineProps<{
  items: ScholarshipScoreTeacher[]
}>()
</script>

<template>
  <v-table>
    <tbody>
      <tr v-for="item in items" :key="item.teacher.id">
        <td width="350">
          <UiPerson :item="item.teacher" teacher-format="full" />
        </td>
        <td width="150">
          {{ MonthLabel[item.month] }}
        </td>
        <td class="text-gray" width="150">
          <span :class="{ 'text-success': item.scores_count > 0 }">
            {{ item.scores_count }}
          </span>
          / {{ item.total }}
        </td>
        <td width="250">
          оценок поставлено: {{ item.scores_count }}
        </td>
        <td :class="{ 'text-error': item.total - item.scores_count > 0 }">
          нужно поставить: {{ item.total - item.scores_count }}
        </td>
      </tr>
    </tbody>
  </v-table>
</template>
