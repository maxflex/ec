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
          <div>группа</div>
          <div>
            номер {{ group.id }}
          </div>
        </div>
        <div>
          <div>учебный год</div>
          <div v-if="group.year">
            {{ YearLabel[group.year] }}
          </div>
        </div>
        <div>
          <div>программа</div>
          <div v-if="group.program">
            {{ ProgramLabel[group.program] }}
          </div>
        </div>
        <div>
          <div>
            занятий
          </div>
          <div>
            <GroupLessonCounts :item="group" sum-free />
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
      <Schedule
        v-if="selectedTab === 'schedule'"
        :group-id="group.id"
        :year="group.year"
        :program="group.program"
      />
      <GroupVisitsTab v-else-if="selectedTab === 'visits'" :id="group.id" />
      <GroupStudentsTab v-else :group="group" />
    </div>
  </div>
</template>
