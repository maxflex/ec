<script setup lang="ts">
import type { ClientResource } from '~/components/Client'

const route = useRoute()
const { user } = useAuthStore()

if (!user?.is_head_teacher) {
  showError({
    statusCode: 404,
    statusMessage: 'Not found',
  })
}

const tabs = {
  groups: 'группы',
  schedule: 'расписание',
  examScores: 'баллы',
  grades: 'оценки',
  reports: 'отчёты',
  tests: 'тесты',
} as const

const selectedTab = ref<keyof typeof tabs>('groups')

const client = ref<ClientResource>()

async function loadData() {
  const { data } = await useHttp<ClientResource>(`clients/${route.params.id}`)
  client.value = data.value!
}

nextTick(loadData)
</script>

<template>
  <template v-if="client">
    <div class="panel">
      <div class="panel-info no-pointer-events">
        <div>
          <UiAvatar :item="client" />
        </div>
        <div>
          <div>ученик</div>
          <div class="text-truncate">
            {{ formatName(client) }}
            <div v-if="client.phones">
              <PhoneList :items="client.phones" />
            </div>
          </div>
        </div>
        <div>
          <div>представитель</div>
          <div class="text-truncate">
            {{ formatName(client.parent) }}
            <div v-if="client.parent.phones">
              <PhoneList :items="client.parent.phones" />
            </div>
          </div>
        </div>
        <div>
          <div>направления</div>
          <div>
            <ClientDirections :item="client.directions" />
          </div>
        </div>
        <div>
          <div>куратор</div>
          <UiPerson v-if="client.head_teacher" :item="client.head_teacher" />
          <div v-else>
            не установлено
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
    <HeadTeacherClientGroupsTab v-if="selectedTab === 'groups'" :client-id="client.id" />
    <Schedule v-else-if="selectedTab === 'schedule'" :client-id="client.id" show-teeth head-teacher program-filter />
    <ExamScoreTab v-else-if="selectedTab === 'examScores'" :client-id="client.id" />
    <GradeTab v-else-if="selectedTab === 'grades'" :client-id="client.id" />
    <ReportHeadTeacherTab v-else-if="selectedTab === 'reports'" :client-id="client.id" />
    <ClientTestTab v-else-if="selectedTab === 'tests'" :client-id="client.id" />
  </template>
</template>

<style lang="scss">
.page-clients-id.entity-teacher {
  //.v-btn:not(.v-btn--icon) {
  .table-actionss,
  .v-btn:not(.tests-result-id) {
    display: none;
  }
}
</style>
