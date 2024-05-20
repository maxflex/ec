<script setup lang="ts">
import type { Group } from '~/utils/models'
import { PROGRAM } from '~/utils/sment'

function onClick() {
  emit('select')
}

const { group, selectable } = defineProps<{
  group: Group
  selectable?: boolean
}>()

const emit = defineEmits<{
  (e: 'select'): void
}>()
</script>

<template>
  <div
    class="group-item"
    :class="{ 'group-item--selectable': selectable }"
    @click="onClick"
  >
    <div>
      <NuxtLink :to="{ name: 'groups-id', params: { id: group.id } }">
        Группа {{ group.id }}
      </NuxtLink>
    </div>
    <div>
      <NuxtLink :to="{ name: 'teachers-id', params: { id: group.teacher.id } }">
        {{ formatName(group.teacher) }}
      </NuxtLink>
    </div>
    <div>
      {{ PROGRAM[group.program] }}
    </div>
    <div>{{ group.lessons_planned }} уроков</div>
    <div
      v-if="group.zoom"
      class="text-gray"
      style="flex: none; width: 80vw"
    >
      Идентификатор ZOOM: {{ group.zoom.id }} <br>
      Пароль ZOOM: {{ group.zoom.password }}
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
