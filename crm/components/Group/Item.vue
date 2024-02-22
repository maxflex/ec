<script setup lang="ts">
import type { Group } from "~/utils/models"
import { PROGRAM } from "~/utils/sment"

const { group } = defineProps<{ group: Group }>()
</script>

<template>
  <div class="group-item">
    <div>
      <NuxtLink :to="{ name: 'groups-id', params: { id: group.id } }">
        Группа {{ group.id }}
      </NuxtLink>
    </div>
    <div>
      <a href="#">
        {{ formatName(group.teacher) }}
      </a>
    </div>
    <div>
      {{ PROGRAM[group.program] }}
    </div>
    <div>{{ group.lessons_planned }} уроков</div>
    <div class="text-gray" style="flex: none; width: 80vw" v-if="group.zoom">
      Идентификатор ZOOM: {{ group.zoom.id }} <br />
      Пароль ZOOM: {{ group.zoom.password }}
    </div>
  </div>
</template>

<style lang="scss">
.group-item {
  padding: 16px !important;
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
}
</style>
