<script setup lang="ts">
import type { ClientDialog, PrintSpravkaDialog } from '#build/components'
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
  webReviews: 'отзывы',
  tests: 'тесты',
  logs: 'логи',
  clientComplaints: 'жалобы',
  clientReviews: 'отзывы',
})

const route = useRoute()
const client = ref<ClientResource>()
const clientDialog = ref<InstanceType<typeof ClientDialog>>()

const printSpravkaDialog = ref<InstanceType<typeof PrintSpravkaDialog>>()

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
      <div class="panel-info">
        <UiAvatar :item="client" :size="120" />
        <div>
          <div>
            ученик
            <ClientRiskLabel :item="client" />
          </div>
          <div class="text-truncate">
            {{ formatName(client) }}
            <div v-if="client.phones" class="mt-5">
              <PhoneList :items="client.phones" show-icons />
            </div>
          </div>
        </div>
        <div>
          <div>представитель</div>
          <div class="text-truncate">
            {{ formatName(client.representative) }}
            <div v-if="client.representative.phones" class="mt-5">
              <PhoneList :items="client.representative.phones" show-icons />
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
        <div class="panel-actions">
          <CommentBtn
            :entity-id="client.id"
            :entity-type="EntityTypeValue.client"
          />
          <v-btn
            icon="$print"
            :size="48"
            variant="plain"
            @click="printSpravkaDialog?.open(client.id)"
          />
          <v-btn
            icon="$edit"
            :size="48"
            variant="plain"
            @click="clientDialog?.edit(client.id)"
          />
        </div>
      </div>
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
    <ClientComplaintTab v-else-if="selectedTab === 'clientComplaints'" :client-id="client.id" />
    <ClientReviewTab v-else-if="selectedTab === 'clientReviews'" :client-id="client.id" />
    <Schedule v-else :client-id="client.id" program-filter show-holidays />
    <ClientDialog ref="clientDialog" @updated="onClientUpdated" />
  </template>
  <PrintSpravkaDialog ref="printSpravkaDialog" />
</template>

<style lang="scss">
.page-clients-id {
  .last-seen-at {
    font-size: 14px;
    margin-left: 2px;
  }
}
</style>
