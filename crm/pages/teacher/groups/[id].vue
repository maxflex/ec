<script setup lang="ts">
const tabs = {
  schedule: 'расписание',
  visits: 'посещаемость',
  students: 'ученики',
} as const

const selectedTab = ref<keyof typeof tabs>('schedule')

const route = useRoute()
const group = ref<GroupResource>()

async function loadData() {
  const { data } = await useHttp(`groups/${route.params.id}`)
  group.value = data.value as GroupResource
}

nextTick(loadData)
</script>

<template>
  <div
    v-if="group"
    class="group"
  >
    <div class="panel">
      <div class="panel-info">
        <div>
          <h2 style="font-size: 28px">
            Группа {{ group.id }}
          </h2>
        </div>

        <div v-if="group.teachers.length">
          <div>преподаватели</div>
          <div v-for="t in group.teachers" :key="t.id">
            <RouterLink :to="{ name: 'teachers-id', params: { id: t.id } }">
              {{ formatNameInitials(t) }}
            </RouterLink>
          </div>
        </div>
        <div v-else>
          <div></div>
          <div class="text-gray">
            преподавателей нет
          </div>
        </div>
        <div>
          <div>программа</div>
          <div v-if="group.program">
            {{ ProgramLabel[group.program] }}
          </div>
        </div>

        <div>
          <div>учебный год</div>
          <div v-if="group.year">
            {{ YearLabel[group.year] }}
          </div>
        </div>

        <div>
          <div>
            численность
          </div>
          <div v-if="group.client_groups_count">
            {{ group.client_groups_count }} уч.
          </div>
          <div v-else class="text-gray">
            0 уч.
          </div>
        </div>

        <div>
          <div>уроки</div>
          <div v-if="group.lessons.conducted || group.lessons.planned">
            <GroupLessonCounts :item="group" />
          </div>
          <div v-else class="text-gray">
            уроков нет
          </div>
        </div>

        <div>
          <div>
            zoom
          </div>
          <div>
            <UiIfSet :value="group.zoom.id">
              {{ group.zoom.id }} / {{ group.zoom.password }}
            </UiIfSet>
          </div>
        </div>
      </div>

      <div class="tabs">
        <div
          v-for="(label, key) in tabs"
          :key="key"
          class="tabs-item"
          :class="{ 'tabs-item--active': selectedTab === key }"
          @click="selectedTab = key"
        >
          {{ label }}
        </div>
      </div>
    </div>
    <div>
      <Schedule v-if="selectedTab === 'schedule'" :group="group" />
      <GroupVisitsTab v-else-if="selectedTab === 'visits'" :id="group.id" />
      <GroupStudentsTab v-else :group="group" />
    </div>
  </div>
</template>

<style lang="scss">
.page-groups-id {
  .panel-info {
    align-items: center;
  }
}
</style>
