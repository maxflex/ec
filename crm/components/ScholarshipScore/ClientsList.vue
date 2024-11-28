<script setup lang="ts">
const { items } = defineProps<{
  items: ScholarshipScoreClient[]
}>()
</script>

<template>
  <v-table>
    <tbody>
      <tr v-for="item in items" :key="item.client.id">
        <td width="300">
          <UiPerson :item="item.client" />
        </td>
        <td width="200">
          {{ MonthLabel[item.month] }}
        </td>
        <td width="200">
          <template v-if="item.scores_count">
            {{ plural(item.scores_count, ['оценка', 'оценки', 'оценок']) }}
          </template>
          <span v-else class="text-gray">
            нет оценок
          </span>
        </td>
        <td>
          <ScholarshipScoreCircle :score="item.avg_score" float />
        </td>
      </tr>
    </tbody>
  </v-table>
</template>
