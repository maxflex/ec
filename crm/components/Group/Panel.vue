<script setup lang="ts">
const { item } = defineProps<{
  item: GroupResource
}>()
</script>

<template>
  <div class="panel-info">
    <div>
      <h2 style="font-size: 28px">
        Группа {{ item.id }}
      </h2>
    </div>

    <div>
      <div>преподаватели</div>
      <GroupTeachers :item="item" :no-link="false" />
    </div>

    <div>
      <div>программа</div>
      <div v-if="item.program">
        {{ ProgramLabel[item.program] }}
      </div>
    </div>

    <div>
      <div>уроки</div>
      <div>
        <UiIfSet :value="item.lesson_counts.conducted || item.lesson_counts.planned">
          <template #empty>
            уроков нет
          </template>
          <GroupLessonCounts :item="item" />
        </UiIfSet>
      </div>
    </div>

    <div>
      <div>
        zoom
      </div>
      <div>
        <UiIfSet :value="item.zoom.id">
          {{ item.zoom.id }} / {{ item.zoom.password }}
        </UiIfSet>
      </div>
    </div>

    <div class="panel-actions">
      <slot name="actions" />
    </div>
  </div>
</template>
