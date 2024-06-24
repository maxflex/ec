<script setup lang="ts">
const tabs = {
  schedule: 'расписание',
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
          <div>группа {{ group.id }}</div>
          <div>
            {{ group.is_archived ? 'заархивирована' : 'активна' }}
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
          <div>zoom</div>
          <div>
            {{ group.zoom?.id || 'не установлено' }}
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
    <div v-if="selectedTab === 'schedule'">
      <ScheduleList :id="group.id!" entity="group" conductable />
    </div>
  </div>
</template>
