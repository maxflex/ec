<script setup lang="ts">
const { items } = defineProps<{
  items: ScholarshipScoreResource[]
}>()
</script>

<template>
  <v-table>
    <tbody>
      <tr v-for="(item, index) in items" :key="index">
        <td width="300">
          <UiPerson :item="item.client" />
        </td>
        <td width="150">
          {{ ProgramShortLabel[item.program] }}
        </td>
        <td width="150" class="text-lowercase">
          {{ MonthLabel[item.month] }}
        </td>
        <td width="150">
          {{ plural(item.lessons_count, ["занятие", "занятия", "занятий"]) }}
        </td>
        <td width="150">
          <ScholarshipScoreCircle v-if="item.id" :score="item.score!" />
          <span v-else class="text-gray">
            нет оценки
          </span>
        </td>
        <td v-if="item.id" class="text-sm-right">
          <!--          edit -->
        </td>
        <td v-else class="text-md-right pa-0" width="100">
          <!--          create -->
        </td>
      </tr>
    </tbody>
  </v-table>
</template>
