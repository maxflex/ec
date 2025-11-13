<script setup lang="ts">
import type { ClientResource } from '~/components/Client'

const { tabs, selectedTab } = useTabs({
  requests: 'заявки',
  contracts: 'договоры',
  schedule: 'расписание',
  events: 'события',
  groups: 'группы',
  examScores: 'баллы',
  markSheet: 'ведомость',
  grades: 'оценки',
  reports: 'отчёты',
  clientReviews: 'отзывы',
  webReviews: 'отзывы на сайте',
  tests: 'тесты',
  logs: 'логи',
  complaints: 'жалобы',
  violations: 'нарушения',
})

const route = useRoute()
const client = ref<ClientResource>()

async function loadData() {
  const { data } = await useHttp<ClientResource>(`clients/${route.params.id}`)
  client.value = data.value!
  if (route.query.contract_id || route.hash === '#contracts') {
    selectedTab.value = 'contracts'
  }
}

function onClientUpdated(c: ClientResource) {
  client.value = c
}

nextTick(loadData)
</script>

<template>
  <template v-if="client">
    <div class="panel">
      <ClientPanel :item="client" @updated="onClientUpdated" />
      <div v-if="client.schedule" class="panel-schedule">
        <TeethBar :items="client.schedule" />
        <LessonCurrentLesson :item="client.current_lesson" />
      </div>
      <UiTabs v-model="selectedTab" :items="tabs" />
    </div>

    <RequestTab v-if="selectedTab === 'requests'" :client-id="client.id" />
    <ReportTab v-else-if="selectedTab === 'reports'" :client-id="client.id" />
    <ContractTab v-else-if="selectedTab === 'contracts'" :client-id="client.id" />
    <WebReviewTab v-else-if="selectedTab === 'webReviews'" :client-id="client.id" />
    <ExamScoreTab v-else-if="selectedTab === 'examScores'" :client-id="client.id" />
    <GradeTab v-else-if="selectedTab === 'grades'" :client-id="client.id" />
    <ClientGroupsTab v-else-if="selectedTab === 'groups'" :client="client" />
    <ClientTestTab v-else-if="selectedTab === 'tests'" :client-id="client.id" />
    <EventTab v-else-if="selectedTab === 'events'" :client-id="client.id" />
    <LogTab v-else-if="selectedTab === 'logs'" :client-id="client.id" />
    <ClientMarkSheetTab v-else-if="selectedTab === 'markSheet'" :client="client" />
    <ComplaintTab v-else-if="selectedTab === 'complaints'" :client-id="client.id" />
    <ClientReviewTab v-else-if="selectedTab === 'clientReviews'" :client-id="client.id" />
    <ViolationTab v-else-if="selectedTab === 'violations'" :client-id="client.id" />
    <Schedule v-else :client-id="client.id" program-filter show-holidays show-calendar />
  </template>
</template>
