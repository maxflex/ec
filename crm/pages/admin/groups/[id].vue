<script setup lang="ts">
import type { GroupDialog, PrintDialog } from '#build/components'

const { tabs, selectedTab, tabCounts, tabCountsExtra } = useTabs({
  schedule: 'расписание',
  visits: 'посещаемость',
  students: 'ученики',
  acts: 'акты',
})

const route = useRoute()
const group = ref<GroupResource>()
const groupDialog = ref<InstanceType<typeof GroupDialog>>()
const printDialog = ref<InstanceType<typeof PrintDialog>>()

const printOptions: PrintOption[] = [
  { id: 13, label: 'Договор на преподавателя (ООО)', company: 'ooo' },
  { id: 17, label: 'Договор на преподавателя 8, 9 кл (ООО)', company: 'ooo' },
  { id: 13, label: 'Договор на преподавателя (ИП)', company: 'ip' },
  { id: 17, label: 'Договор на преподавателя 8, 9 кл (ИП)', company: 'ip' },
]

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
          <v-menu>
            <template #activator="{ props }">
              <v-btn
                v-bind="props"
                icon="$print"
                :size="48"
                variant="plain"
              />
            </template>
            <v-list>
              <v-list-item
                v-for="p in printOptions"
                :key="p.label"
                @click="printDialog?.open(p, { group_id: group.id })"
              >
                {{ p.label }}
              </v-list-item>
            </v-list>
          </v-menu>
          <v-btn
            icon="$edit"
            :size="48"
            variant="plain"
            @click="groupDialog?.edit(group!)"
          />
        </template>
      </GroupPanel>

      <UiTabs v-model="selectedTab" :items="tabs" :counts="tabCounts" :counts-extra="tabCountsExtra" />
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
  <LazyPrintDialog ref="printDialog" />
</template>
