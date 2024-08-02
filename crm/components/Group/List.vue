<script setup lang="ts">
import { mdiAccountGroup } from '@mdi/js'

const { items, selectable } = defineProps<{
  items: GroupListResource[]
  selectable?: boolean
}>()

const emit = defineEmits<{
  select: [g: GroupListResource]
}>()

function onClick(g: GroupListResource) {
  if (selectable) {
    emit('select', g)
  }
}
</script>

<template>
  <div
    v-for="item in items"
    :id="`group-${item.id}`"
    :key="item.id"
    class="group-item"
    :class="{ 'group-item--selectable': selectable }"
    @click="onClick(item)"
  >
    <div style="width: 180px">
      <NuxtLink :to="{ name: 'groups-id', params: { id: item.id } }">
        Группа {{ item.id }}
      </NuxtLink>
    </div>
    <div style="width: 200px">
      <div v-for="t in item.teachers" :key="t.id">
        <RouterLink :to="{ name: 'teachers-id', params: { id: t.id } }">
          {{ formatNameInitials(t) }}
        </RouterLink>
      </div>
    </div>
    <div style="width: 120px">
      {{ ProgramShortLabel[item.program] }}
    </div>
    <div style="width: 120px">
      <template v-if="item.lessons_count">
        {{ plural(item.lessons_count, ['урок', 'урока', 'уроков']) }}
      </template>
    </div>
    <div style="width: 80px">
      <v-icon :icon="mdiAccountGroup" class="mr-2" style="top: -3px; position: relative;" />
      {{ item.group_contracts_count }}
    </div>
    <div>
      <Teeth :items="item.teeth" />
    </div>
  </div>
</template>

<style lang="scss">
.group-item {
  &--selectable {
    cursor: pointer;
  }
}
</style>
