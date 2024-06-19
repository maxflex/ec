<script setup lang="ts">
const { items, selectable } = defineProps<{
  items: GroupListResource[]
  selectable?: boolean
}>()

const emit = defineEmits<{
  select: [g: GroupListResource]
}>()

function onClick(g: GroupListResource) {
  selectable && emit('select', g)
}
</script>

<template>
  <div
    v-for="item in items"
    :key="item.id"
    class="group-item"
    :class="{ 'group-item--selectable': selectable }"
    @click="onClick(item)"
  >
    <div>
      <NuxtLink :to="{ name: 'groups-id', params: { id: item.id } }">
        Группа {{ item.id }}
      </NuxtLink>
    </div>
    <div />
    <div>
      {{ ProgramLabel[item.program] }}
    </div>
    <div>
      <template v-if="item.lessons_count">
        {{ plural(item.lessons_count, ['урок', 'урока', 'уроков']) }}
      </template>
    </div>
    <div
      v-if="item.zoom"
      class="text-gray"
      style="flex: none; width: 80vw"
    >
      Идентификатор ZOOM: {{ item.zoom.id }} <br>
      Пароль ZOOM: {{ item.zoom.password }}
    </div>
  </div>
</template>

<style lang="scss">
.group-item {
  & > div {
    &:nth-child(1) {
      width: 200px;
    }
    &:nth-child(2) {
      width: 250px;
    }
    &:nth-child(3) {
      width: 350px;
    }
  }
  &--selectable {
    cursor: pointer;
  }
}
</style>
