<script setup lang="ts">
const { client, program, items } = defineProps<{
  client: PersonResource
  program: Program
  items: ClientLessonResource[]
}>()
</script>

<template>
  <v-table class="list-in-grades">
    <tbody>
      <tr>
        <td colspan="2">
          Ученик:
          <UiPerson :item="client" />
        </td>
        <td colspan="10">
          Программа:
          {{ ProgramLabel[program] }}
        </td>
      </tr>
      <tr v-for="item in items" :key="item.id">
        <td width="120">
          {{ formatDate(item.lesson.date) }}
        </td>
        <td width="150">
          <span :class="{ 'text-error': item.status === 'absent' }">
            {{ ClientLessonStatusLabel[item.status] }}
          </span>
          <template v-if="item.minutes_late">
            на {{ item.minutes_late }} мин.
          </template>
        </td>
        <td width="180">
          <UiPerson :item="item.lesson.teacher" />
        </td>
        <td>
          <template v-if="item.lesson.topic">
            {{ item.lesson.topic }}
          </template>
        </td>
        <td width="300">
          <div v-if="item.scores.length" class="list-in-grades__scores">
            <div v-for="(score, i) in item.scores" :key="i">
              <span :class="`score score--${score.score}`">
                {{ score.score }}
              </span>
              <div>
                {{ score.comment }}
              </div>
            </div>
          </div>
        </td>
      </tr>
    </tbody>
  </v-table>
</template>

<style lang="scss">
.list-in-grades {
  tr:first-child td {
    background: #f5f5f5;
  }
  td {
    padding: 16px 16px !important;
    min-height: auto !important;
  }
  &__scores {
    display: flex;
    flex-direction: column;
    gap: 10px;
    margin-top: 4px;
    & > div {
      display: flex;
      align-items: center;
      gap: 8px;
    }
  }
}
</style>
