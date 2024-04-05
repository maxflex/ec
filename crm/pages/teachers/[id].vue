<script setup lang="ts">
import type { Teacher } from "~/utils/models"
import { ENTITY_TYPE } from "~/utils/sment"

const route = useRoute()
const teacher = ref<Teacher>()

async function loadData() {
  const { data } = await useHttp<Teacher>(`teachers/${route.params.id}`)
  if (data.value) {
    teacher.value = data.value
  }
}

nextTick(loadData)
</script>

<template>
  <div class="teacher" v-if="teacher">
    <div>
      <h3>
        Преподаватель
        <PreviewModeBtn
          :user="{
            id: teacher.id,
            entity_type: ENTITY_TYPE.teacher,
          }"
        />
      </h3>
      <div class="inputs">
        <v-text-field v-model="teacher.last_name" label="Фамилия" />
        <v-text-field v-model="teacher.first_name" label="Имя" />
        <v-text-field v-model="teacher.middle_name" label="Отчество" />
      </div>
    </div>
  </div>
</template>

<style lang="scss">
.teacher {
  padding: 20px;
  h3 {
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    .v-btn {
      margin-left: 2px;
    }
    .v-icon {
      font-size: calc(var(--v-icon-size-multiplier) * 1.5rem) !important;
    }
  }
}
</style>
