<script setup lang="ts">
const tabs = {
  schedule: 'расписание',
  visits: 'посещаемость',
  students: 'ученики',
} as const

type Tab = keyof typeof tabs
type TabCounts = Partial<Record<Tab, number>>

const selectedTab = ref<keyof typeof tabs>('schedule')
const tabCounts = ref<TabCounts>({})

const route = useRoute()
const group = ref<GroupResource>()

async function loadData() {
  const { data } = await useHttp(`groups/${route.params.id}`)
  group.value = data.value as GroupResource
  tabCounts.value.students = group.value.client_groups_count
}

nextTick(loadData)
</script>

<template>
  <div
    v-if="group"
    class="group"
  >
    <div class="panel">
      <GroupPanel :item="group" />
      <div class="tabs">
        <div
          v-for="(label, key) in tabs"
          :key="key"
          class="tabs-item"
          :class="{ 'tabs-item--active': selectedTab === key }"
          @click="selectedTab = key"
        >
          {{ label }}
          <v-badge
            v-if="tabCounts[key]"
            color="grey-darken-3"
            inline
            :content="tabCounts[key]"
          />
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
