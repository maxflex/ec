<script setup lang="ts">
import { ScholarshipScoreDialog } from '#components'

const { items } = defineProps<{
  items: ScholarshipScoreResource[]
}>()

const emit = defineEmits(['updated'])

const dialog = ref<InstanceType<typeof ScholarshipScoreDialog>>()
</script>

<template>
  <v-table class="scholarship-score-list">
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
        <td>
          <div class="table-actionss">
            <v-btn
              icon="$edit"
              :size="48"
              variant="plain"
              @click="dialog?.open(item)"
            />
          </div>
        </td>
      </tr>
    </tbody>
  </v-table>
  <ScholarshipScoreDialog ref="dialog" @updated="emit('updated')" />
</template>

<style lang="scss">
.scholarship-score-list {
  tr {
    position: relative;
  }
  td > .table-actionss {
    top: -3px !important;
  }
}
</style>
