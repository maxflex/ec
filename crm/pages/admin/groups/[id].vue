<script setup lang="ts">
import type { GroupDialog, PrintDialog } from '#build/components'

const tabs = {
  schedule: 'расписание',
  visits: 'посещаемость',
  students: 'ученики',
  acts: 'акты',
} as const

const selectedTab = ref<keyof typeof tabs>('schedule')

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
}

function onGroupDeleted() {
  navigateTo('/groups')
}

nextTick(loadData)
</script>

<template>
  <template v-if="group">
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
          <div>
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
            {{ group.zoom.id }} / {{ group.zoom.password }}
          </div>
        </div>

        <div class="panel-actions">
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
    <Schedule v-if="selectedTab === 'schedule'" :group="group" />
    <GroupVisitsTab v-else-if="selectedTab === 'visits'" :id="group.id" />
    <GroupActTab v-else-if="selectedTab === 'acts'" :id="group.id" />
    <GroupStudentsTab v-else :group="group" />
  </template>
  <GroupDialog
    ref="groupDialog"
    @updated="g => (group = g)"
    @deleted="onGroupDeleted"
  />
  <LazyPrintDialog ref="printDialog" />
</template>

<style lang="scss">
.page-groups-id {
  .panel-info {
    align-items: center;
  }
}
</style>
