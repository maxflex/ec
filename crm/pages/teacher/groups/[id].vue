<script setup lang="ts">
import type { GroupResource } from '~/components/Group'

const { tabs, selectedTab, tabCounts } = useTabs({
  schedule: 'расписание',
  visits: 'посещаемость',
  students: 'ученики',
})

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
  <div v-if="group" class="group">
    <div class="panel">
      <GroupPanel :item="group" />
      <UiTabs v-model="selectedTab" :items="tabs" />
    </div>
    <div>
      <Schedule v-if="selectedTab === 'schedule'" :group="group" show-holidays />
      <GroupVisitsTab v-else-if="selectedTab === 'visits'" :id="group.id" />
      <GroupStudentsTab v-else :group="group" />
    </div>
  </div>
</template>
