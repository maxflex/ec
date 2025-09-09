<script setup lang="ts">
import type { GroupDialog } from '#build/components'
import type { GroupResource } from '~/components/Group'

const { tabs, selectedTab, tabCounts, tabCountsExtra } = useTabs({
  schedule: 'расписание',
  visits: 'посещаемость',
  students: 'ученики',
  acts: 'акты',
})

const route = useRoute()
const group = ref<GroupResource>()
const groupDialog = ref<InstanceType<typeof GroupDialog>>()

async function loadData() {
  const { data } = await useHttp(`groups/${route.params.id}`)
  group.value = data.value as GroupResource
  tabCounts.value.students = group.value.client_groups_count
  tabCounts.value.acts = group.value.acts_count
  tabCountsExtra.value.students = group.value.draft_students_count
}

function onGroupDeleted() {
  navigateTo('/groups')
}

nextTick(loadData)
</script>

<template>
  <template v-if="group">
    <div class="panel">
      <GroupPanel :item="group">
        <div>
          <div>
            кабинеты
          </div>
          <div>
            <UiIfSet :value="group.cabinets.length">
              <template #empty>
                нет
              </template>
              <div v-for="c in group.cabinets" :key="c">
                <CabinetWithCapacity :item="c" />
              </div>
            </UiIfSet>
          </div>
        </div>
        <template #actions>
          <PrintBtn
            :items="[
              { 13: 'ooo' },
              { 17: 'ooo' },
              { 13: 'ip' },
              { 17: 'ip' },
            ]"
            :extra="{ group_id: group.id }"
            variant="plain"
          />
          <v-btn
            icon="$edit"
            :size="48"
            variant="plain"
            @click="groupDialog?.edit(group!)"
          />
        </template>
      </GroupPanel>

      <UiTabs v-model="selectedTab" :items="tabs" :counts="tabCounts" :counts-extra="tabCountsExtra" show-zero />
    </div>
    <Schedule v-if="selectedTab === 'schedule'" :group="group" />
    <GroupVisitsTab v-else-if="selectedTab === 'visits'" :id="group.id" />
    <GroupActTab v-else-if="selectedTab === 'acts'" :id="group.id" />
    <GroupStudentsTab v-else :group="group" @updated="loadData" />
  </template>
  <GroupDialog
    ref="groupDialog"
    @updated="g => (group = g)"
    @deleted="onGroupDeleted"
  />
</template>
