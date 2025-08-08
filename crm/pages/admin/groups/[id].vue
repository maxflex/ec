<script setup lang="ts">
import type { GroupDialog, PrintDialog } from '#build/components'

const tabs = {
  schedule: 'расписание',
  visits: 'посещаемость',
  students: 'ученики',
  acts: 'акты',
} as const

type Tab = keyof typeof tabs
type TabCounts = Partial<Record<Tab, number>>

const selectedTab = ref<Tab>('schedule')
const tabCounts = ref<TabCounts>({})

const route = useRoute()
const group = ref<GroupResource>()
const groupDialog = ref<InstanceType<typeof GroupDialog>>()
const printDialog = ref<InstanceType<typeof PrintDialog>>()

const printOptions: PrintOption[] = [
  { id: 13, label: 'Договор на преподавателя (ООО)', company: 'ooo' },
  { id: 13, label: 'Договор на преподавателя (ИП)', company: 'ip' },
]

async function loadData() {
  const { data } = await useHttp(`groups/${route.params.id}`)
  group.value = data.value as GroupResource
  tabCounts.value.students = group.value.client_groups_count
  tabCounts.value.acts = group.value.acts_count
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
                не установлено
              </template>
              <CabinetWithCapacity :items="group.cabinets" />
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

      <div class="tabs tabs--test">
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
